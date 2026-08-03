<?php

declare(strict_types=1);

use App\Enums\NotificationType;
use App\Mail\AssignmentDeadlineApproaching;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\AssignmentDeadlineNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('routes through mail and database channels, and builds a mailable addressed to the cursist', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'email' => 'cursist@example.test']);
    $assignment = CourseAssignment::factory()
        ->for($reseller, 'reseller')->for($course)->for($cursist, 'user')
        ->withDeadline(now()->addDays(7))->create();

    $notification = new AssignmentDeadlineNotification($assignment, 7);

    expect($notification->via($cursist))->toBe(['mail', 'database']);

    $mail = $notification->toMail($cursist);
    expect($mail)->toBeInstanceOf(AssignmentDeadlineApproaching::class)
        ->and($mail->to[0]['address'])->toBe('cursist@example.test');

    $payload = $notification->toDatabase($cursist);
    expect($payload['type'])->toBe(NotificationType::Deadline->value)
        ->and($payload['message'])->toBeString()
        ->and($payload['course_assignment_id'])->toBe($assignment->id)
        ->and($payload['days_before'])->toBe(7);
});
