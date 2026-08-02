<?php

declare(strict_types=1);

use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Services\Reporting\ResellerDashboardService;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('composes klant/cursist/assignment counts and spend into one snapshot', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->create();
    CourseAssignment::factory()->for($course)->for($cursist, 'user')->create([
        'reseller_id' => $reseller->id,
        'billing_state' => AssignmentBillingState::Billed,
        'price_cents' => 750,
    ]);

    $snapshot = app(ResellerDashboardService::class)->snapshot();

    expect($snapshot->klantCount)->toBe(1)
        ->and($snapshot->assignmentCount)->toBe(1)
        ->and($snapshot->billedSpend->cents)->toBe(750)
        ->and($snapshot->pendingSpend->isZero())->toBeTrue();
});
