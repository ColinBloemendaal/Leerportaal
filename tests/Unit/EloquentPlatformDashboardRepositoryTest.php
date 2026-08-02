<?php

declare(strict_types=1);

use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Repositories\Eloquent\EloquentPlatformDashboardRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('counts resellers, users, and courses across the whole platform', function (): void {
    Reseller::factory()->count(2)->create();
    // platformStaff(): a plain User::factory()->create() would implicitly
    // create its own Reseller (reseller_id defaults to Reseller::factory()),
    // which would silently inflate the reseller count this test asserts.
    User::factory()->count(3)->platformStaff()->create();
    Course::factory()->count(4)->create();

    $repository = new EloquentPlatformDashboardRepository;

    expect($repository->resellerCount())->toBe(2)
        ->and($repository->userCount())->toBe(3)
        ->and($repository->courseCount())->toBe(4);
});

it('sums revenue by billing state across every reseller, regardless of ambient tenant context', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    CourseAssignment::factory()->create(['reseller_id' => $resellerA->id, 'billing_state' => AssignmentBillingState::Billed, 'price_cents' => 1000]);
    CourseAssignment::factory()->create(['reseller_id' => $resellerB->id, 'billing_state' => AssignmentBillingState::Billed, 'price_cents' => 2500]);
    CourseAssignment::factory()->create(['reseller_id' => $resellerA->id, 'billing_state' => AssignmentBillingState::Pending, 'price_cents' => 500]);
    CourseAssignment::factory()->create(['reseller_id' => $resellerA->id, 'billing_state' => AssignmentBillingState::Waived, 'price_cents' => 999]);

    // No ambient TenantContext is set at all -- proves the repository
    // bypasses the fails-closed TenantScope rather than depending on it.
    $repository = new EloquentPlatformDashboardRepository;

    expect($repository->billedRevenueCents())->toBe(3500)
        ->and($repository->pendingRevenueCents())->toBe(500);
});
