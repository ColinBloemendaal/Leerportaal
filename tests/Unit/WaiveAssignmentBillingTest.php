<?php

declare(strict_types=1);

use App\Actions\Billing\WaiveAssignmentBilling;
use App\Enums\AssignmentBillingState;
use App\Enums\InvoiceStatus;
use App\Models\CourseAssignment;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('removes the line and reduces the draft invoice total, then waives the assignment', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->create(['subtotal_cents' => 5000]);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')
        ->create(['billing_state' => AssignmentBillingState::Billed, 'price_cents' => 1500]);
    InvoiceLine::factory()->for($invoice)->for($assignment, 'courseAssignment')->create(['amount_cents' => 1500]);

    app(WaiveAssignmentBilling::class)($assignment);

    expect($assignment->fresh()->billing_state)->toBe(AssignmentBillingState::Waived)
        ->and($invoice->fresh()->subtotal_cents->cents)->toBe(3500)
        ->and(InvoiceLine::query()->where('course_assignment_id', $assignment->id)->exists())->toBeFalse();
});

it('waives the assignment, leaves an already-issued invoice untouched, and issues a credit note instead', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create(['total_cents' => 5000]);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')
        ->create(['billing_state' => AssignmentBillingState::Billed, 'price_cents' => 1500]);
    InvoiceLine::factory()->for($invoice)->for($assignment, 'courseAssignment')->create(['amount_cents' => 1500]);

    app(WaiveAssignmentBilling::class)($assignment);

    $creditNote = CreditNote::query()->where('invoice_id', $invoice->id)->first();

    expect($assignment->fresh()->billing_state)->toBe(AssignmentBillingState::Waived)
        ->and($invoice->fresh()->total_cents->cents)->toBe(5000)
        ->and($invoice->fresh()->status)->toBe(InvoiceStatus::Issued)
        ->and(InvoiceLine::query()->where('course_assignment_id', $assignment->id)->exists())->toBeTrue()
        ->and($creditNote)->not->toBeNull()
        ->and($creditNote->amount_cents->cents)->toBe(1500)
        ->and($creditNote->reason)->toBe('14-day free revocation');
});

it('does nothing when the assignment was never billed', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')
        ->create(['billing_state' => AssignmentBillingState::Pending]);

    app(WaiveAssignmentBilling::class)($assignment);

    expect($assignment->fresh()->billing_state)->toBe(AssignmentBillingState::Pending);
});
