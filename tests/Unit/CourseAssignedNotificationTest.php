<?php

declare(strict_types=1);

use App\Enums\NotificationType;
use App\Mail\CourseAssigned;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\CourseAssignedNotification;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($this->reseller);
});

it('routes through mail and database channels', function (): void {
    $cursist = User::factory()->create(['reseller_id' => $this->reseller->id]);
    $assignment = CourseAssignment::factory()->create([
        'reseller_id' => $this->reseller->id,
        'user_id' => $cursist->id,
    ]);

    expect((new CourseAssignedNotification($assignment))->via($cursist))->toBe(['mail', 'database']);
});

it('builds the CourseAssigned mailable addressed to the cursist', function (): void {
    $cursist = User::factory()->create(['reseller_id' => $this->reseller->id, 'email' => 'cursist@example.test']);
    $assignment = CourseAssignment::factory()->create([
        'reseller_id' => $this->reseller->id,
        'user_id' => $cursist->id,
    ]);

    $mail = (new CourseAssignedNotification($assignment))->toMail($cursist);

    expect($mail)->toBeInstanceOf(CourseAssigned::class)
        ->and($mail->assignment->is($assignment))->toBeTrue()
        ->and($mail->to)->toHaveCount(1)
        ->and($mail->to[0]['address'])->toBe('cursist@example.test');
});

it('stores a typed database payload with a rendered message', function (): void {
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $cursist = User::factory()->create(['reseller_id' => $this->reseller->id]);
    $assignment = CourseAssignment::factory()->create([
        'reseller_id' => $this->reseller->id,
        'course_id' => $course->id,
        'user_id' => $cursist->id,
    ]);

    $payload = (new CourseAssignedNotification($assignment))->toDatabase($cursist);

    expect($payload['type'])->toBe(NotificationType::Assignment->value)
        ->and($payload['message'])->toBe('You have been assigned Fire Safety 101')
        ->and($payload['course_assignment_id'])->toBe($assignment->id)
        ->and($payload['course_id'])->toBe($course->id);
});
