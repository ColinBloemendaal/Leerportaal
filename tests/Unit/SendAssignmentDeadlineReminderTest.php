<?php

declare(strict_types=1);

use App\Actions\Notifications\SendAssignmentDeadlineReminder;
use App\Actions\Progress\MarkBlockCompleted;
use App\Models\AssignmentReminder;
use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Reseller;
use App\Notifications\AssignmentDeadlineNotification;
use App\Notifications\AssignmentOverdueNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function reminderAction(): SendAssignmentDeadlineReminder
{
    return app(SendAssignmentDeadlineReminder::class);
}

function assignmentWithCourse(Reseller $reseller, array $assignmentAttributes = []): array
{
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();

    $assignment = CourseAssignment::factory()
        ->for($reseller, 'reseller')
        ->create(array_merge(['course_id' => $course->id], $assignmentAttributes));

    return [$assignment, $course, $block];
}

it('sends and records an overdue notification exactly once', function (): void {
    Notification::fake();
    $reseller = Reseller::factory()->create();
    [$assignment] = assignmentWithCourse($reseller, ['deadline_at' => now()->subDay()]);

    $sent = reminderAction()($assignment->fresh(['course', 'user', 'reseller']), Carbon::today());

    expect($sent)->toBe(1);
    Notification::assertSentTo($assignment->user, AssignmentOverdueNotification::class);
    expect(AssignmentReminder::query()->withoutTenantScope()->count())->toBe(1);

    $sentAgain = reminderAction()($assignment->fresh(['course', 'user', 'reseller']), Carbon::today());

    expect($sentAgain)->toBe(0);
    Notification::assertSentToTimes($assignment->user, AssignmentOverdueNotification::class, 1);
});

it('sends and records a deadline reminder for each due offset, once each', function (): void {
    Notification::fake();
    $reseller = Reseller::factory()->create();
    $deadline = Carbon::parse('2026-09-01 00:00:00');
    [$assignment] = assignmentWithCourse($reseller, [
        'deadline_at' => $deadline,
        'reminder_days_before' => [7, 1],
    ]);

    $sent = reminderAction()($assignment->fresh(['course', 'user', 'reseller']), Carbon::parse('2026-08-25'));

    expect($sent)->toBe(1);
    Notification::assertSentTo(
        $assignment->user,
        AssignmentDeadlineNotification::class,
        fn (AssignmentDeadlineNotification $notification) => true,
    );

    $sentSameDayAgain = reminderAction()($assignment->fresh(['course', 'user', 'reseller']), Carbon::parse('2026-08-25'));

    expect($sentSameDayAgain)->toBe(0);
    Notification::assertSentToTimes($assignment->user, AssignmentDeadlineNotification::class, 1);
});

it('never sends anything once the course has been completed, even past the deadline', function (): void {
    Notification::fake();
    $reseller = Reseller::factory()->create();
    [$assignment, , $block] = assignmentWithCourse($reseller, ['deadline_at' => now()->subDay()]);
    app(MarkBlockCompleted::class)($assignment, $block);

    $sent = reminderAction()($assignment->fresh(['course', 'user', 'reseller']), Carbon::today());

    expect($sent)->toBe(0);
    Notification::assertNothingSent();
});
