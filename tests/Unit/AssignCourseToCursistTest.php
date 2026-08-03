<?php

declare(strict_types=1);

use App\Actions\Courses\AssignCourseToCursist;
use App\DataTransferObjects\Courses\AssignCourseData;
use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\CourseAssignedNotification;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function assignCourseToCursistAction(): AssignCourseToCursist
{
    return app(AssignCourseToCursist::class);
}

beforeEach(function (): void {
    app(TenantContext::class)->set(Reseller::factory()->create());
});

it('creates a pending, priced assignment and notifies the cursist', function (): void {
    Notification::fake();

    $course = Course::factory()->create(['platform_price_cents' => 2500]);
    $cursist = User::factory()->create();
    $admin = User::factory()->create();

    $assignment = assignCourseToCursistAction()($course, new AssignCourseData($cursist->id, $admin->id));

    expect($assignment->user_id)->toBe($cursist->id)
        ->and($assignment->course_id)->toBe($course->id)
        ->and($assignment->assigned_by_user_id)->toBe($admin->id)
        ->and($assignment->price_cents->cents)->toBe(2500)
        ->and($assignment->billing_state)->toBe(AssignmentBillingState::Pending)
        ->and($assignment->assigned_at)->not->toBeNull()
        ->and($assignment->first_opened_at)->toBeNull()
        ->and($assignment->revoked_at)->toBeNull();

    Notification::assertSentTo($cursist, CourseAssignedNotification::class);
});

it('creates a fresh assignment row for a repeat rather than reusing an existing one', function (): void {
    $course = Course::factory()->create();
    $cursist = User::factory()->create();
    $admin = User::factory()->create();

    assignCourseToCursistAction()($course, new AssignCourseData($cursist->id, $admin->id));
    assignCourseToCursistAction()($course, new AssignCourseData($cursist->id, $admin->id));

    expect($course->fresh()->assignments()->count())->toBe(2);
});
