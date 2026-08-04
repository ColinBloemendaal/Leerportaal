<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Services\Billing\ResellerBillingDashboardService;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns an empty dashboard when the reseller has no draft invoice or history', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $stats = app(ResellerBillingDashboardService::class)->forReseller($reseller->id);

    expect($stats->currentPeriodStart)->toBeNull()
        ->and($stats->currentPeriodSubtotal->cents)->toBe(0)
        ->and($stats->klantBreakdown)->toBe([])
        ->and($stats->history)->toBe([]);
});

it('breaks the current period down per klant and lists invoice history', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klantA = ResellerKlant::factory()->for($reseller, 'reseller')->create(['name' => 'Klant A']);
    $klantB = ResellerKlant::factory()->for($reseller, 'reseller')->create(['name' => 'Klant B']);
    $course = Course::factory()->create();

    $cursistA = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klantA->id]);
    $cursistB = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klantB->id]);

    $assignmentA = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursistA, 'user')->create();
    $assignmentB = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursistB, 'user')->create();

    $draft = Invoice::factory()->for($reseller)->create(['subtotal_cents' => 4500]);
    InvoiceLine::factory()->for($draft)->for($assignmentA, 'courseAssignment')->create(['amount_cents' => 1500]);
    InvoiceLine::factory()->for($draft)->for($assignmentB, 'courseAssignment')->create(['amount_cents' => 3000]);

    Invoice::factory()->for($reseller)->paid()->create(['total_cents' => 5000]);

    $stats = app(ResellerBillingDashboardService::class)->forReseller($reseller->id);

    expect($stats->currentPeriodSubtotal->cents)->toBe(4500)
        ->and($stats->klantBreakdown)->toHaveCount(2)
        ->and(collect($stats->klantBreakdown)->firstWhere('klantId', $klantA->id)->subtotal->cents)->toBe(1500)
        ->and(collect($stats->klantBreakdown)->firstWhere('klantId', $klantB->id)->subtotal->cents)->toBe(3000)
        ->and($stats->history)->toHaveCount(1)
        ->and($stats->history[0]->status)->toBe(InvoiceStatus::Paid->value)
        ->and($stats->history[0]->total->cents)->toBe(5000);
});

it('groups a line with no klant (e.g. storage overage) under "Other"', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $draft = Invoice::factory()->for($reseller)->create(['subtotal_cents' => 1000]);
    InvoiceLine::factory()->for($draft)->create(['course_assignment_id' => null, 'amount_cents' => 1000]);

    $stats = app(ResellerBillingDashboardService::class)->forReseller($reseller->id);

    expect($stats->klantBreakdown)->toHaveCount(1)
        ->and($stats->klantBreakdown[0]->klantId)->toBeNull()
        ->and($stats->klantBreakdown[0]->subtotal->cents)->toBe(1000);
});
