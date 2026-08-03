<?php

declare(strict_types=1);

use App\Actions\Courses\RevokeCourseAssignment;
use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('waives a never-opened assignment revoked within 14 days, reversing the draft line', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create();
    $invoice = Invoice::factory()->for($reseller)->create(['subtotal_cents' => 3000]);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create([
        'billing_state' => AssignmentBillingState::Billed,
        'price_cents' => 3000,
        'assigned_at' => now()->subDays(5),
        'first_opened_at' => null,
    ]);
    InvoiceLine::factory()->for($invoice)->for($assignment, 'courseAssignment')->create(['amount_cents' => 3000]);

    $revoked = app(RevokeCourseAssignment::class)($assignment);

    expect($revoked->revoked_at)->not->toBeNull()
        ->and($revoked->billing_state)->toBe(AssignmentBillingState::Waived)
        ->and($invoice->fresh()->subtotal_cents->cents)->toBe(0);
});

it('bills a revoked assignment that was already opened, even within 14 days', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create();
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create([
        'billing_state' => AssignmentBillingState::Billed,
        'assigned_at' => now()->subDays(2),
        'first_opened_at' => now()->subDay(),
    ]);

    $revoked = app(RevokeCourseAssignment::class)($assignment);

    expect($revoked->revoked_at)->not->toBeNull()
        ->and($revoked->billing_state)->toBe(AssignmentBillingState::Billed);
});

it('bills a never-opened assignment revoked after the 14-day window', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create();
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create([
        'billing_state' => AssignmentBillingState::Billed,
        'assigned_at' => now()->subDays(20),
        'first_opened_at' => null,
    ]);

    $revoked = app(RevokeCourseAssignment::class)($assignment);

    expect($revoked->revoked_at)->not->toBeNull()
        ->and($revoked->billing_state)->toBe(AssignmentBillingState::Billed);
});
