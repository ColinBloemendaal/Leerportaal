<?php

declare(strict_types=1);

use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a user, a course, and an assigning user', function (): void {
    $cursist = User::factory()->create();
    $course = Course::factory()->create();
    $admin = User::factory()->create();

    $assignment = CourseAssignment::factory()
        ->for($cursist, 'user')
        ->for($course)
        ->create(['assigned_by_user_id' => $admin->id]);

    expect($assignment->user->is($cursist))->toBeTrue()
        ->and($assignment->course->is($course))->toBeTrue()
        ->and($assignment->assignedBy->is($admin))->toBeTrue();
});

it('defaults to a pending billing state, unopened and not revoked', function (): void {
    $assignment = CourseAssignment::factory()->create();

    expect($assignment->billing_state)->toBe(AssignmentBillingState::Pending)
        ->and($assignment->first_opened_at)->toBeNull()
        ->and($assignment->revoked_at)->toBeNull();
});

it('records when the assignment was first opened and revoked', function (): void {
    $assignment = CourseAssignment::factory()->opened()->revoked()->create();

    expect($assignment->first_opened_at)->not->toBeNull()
        ->and($assignment->revoked_at)->not->toBeNull();
});
