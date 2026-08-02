<?php

declare(strict_types=1);

use App\Actions\Progress\MarkBlockCompleted;
use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Services\Progress\AssignmentDeadlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function deadlineService(): AssignmentDeadlineService
{
    return app(AssignmentDeadlineService::class);
}

function courseWithOneBlock(): array
{
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();

    return [$course, $block];
}

it('is not overdue when there is no deadline', function (): void {
    [$course] = courseWithOneBlock();
    $assignment = CourseAssignment::factory()->create();

    expect(deadlineService()->isOverdue($assignment, $course))->toBeFalse();
});

it('is not overdue while the deadline is still in the future', function (): void {
    [$course] = courseWithOneBlock();
    $assignment = CourseAssignment::factory()->withDeadline(now()->addDays(3))->create();

    expect(deadlineService()->isOverdue($assignment, $course))->toBeFalse();
});

it('is overdue once the deadline has passed and the course is incomplete', function (): void {
    [$course] = courseWithOneBlock();
    $assignment = CourseAssignment::factory()->withDeadline(now()->subDay())->create();

    expect(deadlineService()->isOverdue($assignment, $course))->toBeTrue();
});

it('is not overdue once the course has been completed, even past the deadline', function (): void {
    [$course, $block] = courseWithOneBlock();
    $assignment = CourseAssignment::factory()->withDeadline(now()->subDay())->create();
    app(MarkBlockCompleted::class)($assignment, $block);

    expect(deadlineService()->isOverdue($assignment, $course))->toBeFalse();
});

it('is not overdue once the assignment has been revoked', function (): void {
    [$course] = courseWithOneBlock();
    $assignment = CourseAssignment::factory()->withDeadline(now()->subDay())->revoked()->create();

    expect(deadlineService()->isOverdue($assignment, $course))->toBeFalse();
});

it('reports the reminder offsets due on a given date', function (): void {
    $deadline = Carbon::parse('2026-09-01 00:00:00');
    $assignment = CourseAssignment::factory()
        ->withDeadline($deadline)
        ->create(['reminder_days_before' => [7, 1]]);

    expect(deadlineService()->reminderOffsetsDueOn($assignment, Carbon::parse('2026-08-25')))->toBe([7])
        ->and(deadlineService()->reminderOffsetsDueOn($assignment, Carbon::parse('2026-08-31')))->toBe([1])
        ->and(deadlineService()->reminderOffsetsDueOn($assignment, Carbon::parse('2026-08-20')))->toBe([]);
});

it('reports no reminder offsets when there is no deadline', function (): void {
    $assignment = CourseAssignment::factory()->create(['deadline_at' => null]);

    expect(deadlineService()->reminderOffsetsDueOn($assignment, now()))->toBe([]);
});
