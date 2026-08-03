<?php

declare(strict_types=1);

use App\Actions\Courses\AssignCourseToCursist;
use App\DataTransferObjects\Courses\AssignCourseData;
use App\Enums\AssignmentBillingState;
use App\Enums\InvoiceStatus;
use App\Models\Course;
use App\Models\Invoice;
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

it('creates a priced assignment, bills it immediately, and notifies the cursist', function (): void {
    Notification::fake();

    $course = Course::factory()->create(['platform_price_cents' => 2500]);
    $cursist = User::factory()->create();
    $admin = User::factory()->create();

    $assignment = assignCourseToCursistAction()($course, new AssignCourseData($cursist->id, $admin->id));

    expect($assignment->user_id)->toBe($cursist->id)
        ->and($assignment->course_id)->toBe($course->id)
        ->and($assignment->assigned_by_user_id)->toBe($admin->id)
        ->and($assignment->price_cents->cents)->toBe(2500)
        // Billed immediately, not left Pending -- CLAUDE.md §11 makes
        // assignment itself the billable event; the 14-day free-revocation
        // rule is a later reversal on top of this, not a delay on billing.
        ->and($assignment->billing_state)->toBe(AssignmentBillingState::Billed)
        ->and($assignment->assigned_at)->not->toBeNull()
        ->and($assignment->first_opened_at)->toBeNull()
        ->and($assignment->revoked_at)->toBeNull();

    Notification::assertSentTo($cursist, CourseAssignedNotification::class);
});

it('records the billable event as an invoice line on the reseller\'s current draft invoice', function (): void {
    $course = Course::factory()->create(['platform_price_cents' => 2500]);
    $cursist = User::factory()->create();
    $admin = User::factory()->create();

    $assignment = assignCourseToCursistAction()($course, new AssignCourseData($cursist->id, $admin->id));

    $invoice = Invoice::query()->where('reseller_id', $assignment->reseller_id)->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->subtotal_cents->cents)->toBe(2500)
        ->and($invoice->lines()->count())->toBe(1)
        ->and($invoice->lines()->first()->course_assignment_id)->toBe($assignment->id)
        ->and($invoice->lines()->first()->amount_cents->cents)->toBe(2500);
});

it('creates a fresh assignment row for a repeat rather than reusing an existing one', function (): void {
    $course = Course::factory()->create();
    $cursist = User::factory()->create();
    $admin = User::factory()->create();

    assignCourseToCursistAction()($course, new AssignCourseData($cursist->id, $admin->id));
    assignCourseToCursistAction()($course, new AssignCourseData($cursist->id, $admin->id));

    expect($course->fresh()->assignments()->count())->toBe(2);
});
