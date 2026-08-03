<?php

declare(strict_types=1);

use App\Actions\Progress\MarkBlockCompleted;
use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Reseller;
use App\Notifications\CourseCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('records progress on the block', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();
    $assignment = CourseAssignment::factory()->for($course)->create();

    $progress = app(MarkBlockCompleted::class)($assignment, $block);

    expect($progress->completed_at)->not->toBeNull();
});

it('notifies the cursist and stamps completed_at exactly once, the moment the course becomes complete', function (): void {
    Notification::fake();
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create();

    app(MarkBlockCompleted::class)($assignment, $block);

    expect($assignment->fresh()->completed_at)->not->toBeNull();
    Notification::assertSentTo($assignment->user, CourseCompletedNotification::class);

    // Marking the same (only) block complete again must not re-notify.
    app(MarkBlockCompleted::class)($assignment, $block);

    Notification::assertSentToTimes($assignment->user, CourseCompletedNotification::class, 1);
});

it('does not notify while the course still has incomplete blocks', function (): void {
    Notification::fake();
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $firstBlock = Block::factory()->for($lesson)->create();
    Block::factory()->for($lesson)->create();
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create();

    app(MarkBlockCompleted::class)($assignment, $firstBlock);

    expect($assignment->fresh()->completed_at)->toBeNull();
    Notification::assertNothingSent();
});
