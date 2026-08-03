<?php

declare(strict_types=1);

use App\Actions\Billing\RecordBillableAssignment;
use App\Enums\AssignmentBillingState;
use App\Enums\InvoiceStatus;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a new draft invoice and line when the reseller has none yet', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create(['title' => 'Fire Safety 101']);
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create(['price_cents' => 1500]);

    $line = app(RecordBillableAssignment::class)($assignment);

    $invoice = Invoice::query()->where('reseller_id', $reseller->id)->first();

    expect($line->description)->toBe('Fire Safety 101')
        ->and($line->amount_cents->cents)->toBe(1500)
        ->and($invoice)->not->toBeNull()
        ->and($invoice->status)->toBe(InvoiceStatus::Draft)
        ->and($invoice->total_cents->cents)->toBe(1500)
        ->and($assignment->fresh()->billing_state)->toBe(AssignmentBillingState::Billed);
});

it('reuses the existing draft invoice and accumulates the total for a second assignment', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create();
    $first = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create(['price_cents' => 1000]);
    $second = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create(['price_cents' => 2000]);

    app(RecordBillableAssignment::class)($first);
    app(RecordBillableAssignment::class)($second);

    expect(Invoice::query()->where('reseller_id', $reseller->id)->count())->toBe(1);

    $invoice = Invoice::query()->where('reseller_id', $reseller->id)->first();

    expect($invoice->total_cents->cents)->toBe(3000)
        ->and($invoice->lines()->count())->toBe(2);
});

it('does not attach a new line to an already-issued invoice', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    Invoice::factory()->for($reseller)->issued()->create();
    $course = Course::factory()->create();
    $assignment = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create(['price_cents' => 1000]);

    app(RecordBillableAssignment::class)($assignment);

    expect(Invoice::query()->where('reseller_id', $reseller->id)->count())->toBe(2)
        ->and(Invoice::query()->where('reseller_id', $reseller->id)->where('status', InvoiceStatus::Draft)->count())->toBe(1);
});
