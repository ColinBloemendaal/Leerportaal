<?php

declare(strict_types=1);

use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Repositories\Eloquent\EloquentResellerDashboardRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('counts only the current reseller\'s klanten, cursisten, and assignments', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    ResellerKlant::factory()->create(['reseller_id' => $otherReseller->id]);

    User::factory()->count(3)->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    // Reseller-side staff (no resellerklant_id) must not count as a cursist.
    $cursistForAssignment = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => null]);
    $otherKlant = ResellerKlant::factory()->create(['reseller_id' => $otherReseller->id]);
    $otherCursist = User::factory()->create(['reseller_id' => $otherReseller->id, 'resellerklant_id' => $otherKlant->id]);

    $course = Course::factory()->create();
    CourseAssignment::factory()->for($course)->for($cursistForAssignment, 'user')->create(['reseller_id' => $reseller->id]);
    CourseAssignment::factory()->for($course)->for($otherCursist, 'user')->create(['reseller_id' => $otherReseller->id]);

    $repository = app(EloquentResellerDashboardRepository::class);

    expect($repository->klantCount())->toBe(2)
        ->and($repository->cursistCount())->toBe(3)
        ->and($repository->assignmentCount())->toBe(1);
});

it('sums spend by billing state for the current reseller only', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $course = Course::factory()->create();
    $cursistA = User::factory()->create(['reseller_id' => $reseller->id]);
    $cursistA2 = User::factory()->create(['reseller_id' => $reseller->id]);
    $cursistB = User::factory()->create(['reseller_id' => $otherReseller->id]);

    CourseAssignment::factory()->for($course)->for($cursistA, 'user')->create([
        'reseller_id' => $reseller->id,
        'billing_state' => AssignmentBillingState::Billed,
        'price_cents' => 1500,
    ]);
    CourseAssignment::factory()->for($course)->for($cursistA2, 'user')->create([
        'reseller_id' => $reseller->id,
        'billing_state' => AssignmentBillingState::Pending,
        'price_cents' => 400,
    ]);
    CourseAssignment::factory()->for($course)->for($cursistB, 'user')->create([
        'reseller_id' => $otherReseller->id,
        'billing_state' => AssignmentBillingState::Billed,
        'price_cents' => 9999,
    ]);

    $repository = app(EloquentResellerDashboardRepository::class);

    expect($repository->billedSpendCents())->toBe(1500)
        ->and($repository->pendingSpendCents())->toBe(400);
});
