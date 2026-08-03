<?php

declare(strict_types=1);

use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\AssignmentDeadlineNotification;
use App\Notifications\AssignmentOverdueNotification;
use Illuminate\Support\Facades\Notification;

function courseWithBlock(): Course
{
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    Block::factory()->for($lesson)->create();

    return $course;
}

it('sends deadline and overdue reminders across every reseller in one run', function (): void {
    Notification::fake();

    $resellerA = Reseller::factory()->create();
    $courseA = courseWithBlock();
    $cursistA = User::factory()->create(['reseller_id' => $resellerA->id]);
    $overdueAssignment = CourseAssignment::factory()
        ->for($resellerA, 'reseller')->for($courseA)->for($cursistA, 'user')
        ->withDeadline(now()->subDay())->create();

    $resellerB = Reseller::factory()->create();
    $courseB = courseWithBlock();
    $cursistB = User::factory()->create(['reseller_id' => $resellerB->id]);
    $dueAssignment = CourseAssignment::factory()
        ->for($resellerB, 'reseller')->for($courseB)->for($cursistB, 'user')
        ->withDeadline(now()->addDays(7))
        ->create(['reminder_days_before' => [7]]);

    $this->artisan('reports:assignment-deadlines')->assertSuccessful();

    Notification::assertSentTo($cursistA, AssignmentOverdueNotification::class);
    Notification::assertSentTo($cursistB, AssignmentDeadlineNotification::class);
});
