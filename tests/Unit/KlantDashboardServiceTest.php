<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Services\Reporting\KlantDashboardService;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('summarizes each cursist\'s assigned/in-progress/completed counts', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id, 'name' => 'Jane Cursist']);
    $course = Course::factory()->create();
    CourseAssignment::factory()->for($course)->for($cursist, 'user')->create(['reseller_id' => $reseller->id]);

    // Reseller staff, not a cursist -- must not appear in the summary.
    User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => null]);

    $snapshot = app(KlantDashboardService::class)->forKlant($klant);

    expect($snapshot->cursistCount)->toBe(1)
        ->and($snapshot->cursisten[0]->userId)->toBe($cursist->id)
        ->and($snapshot->cursisten[0]->name)->toBe('Jane Cursist')
        ->and($snapshot->cursisten[0]->assignedCount)->toBe(1)
        ->and($snapshot->cursisten[0]->completedCount)->toBe(0);
});

it('returns an empty summary for a klant with no cursisten', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $snapshot = app(KlantDashboardService::class)->forKlant($klant);

    expect($snapshot->cursistCount)->toBe(0)
        ->and($snapshot->cursisten)->toBe([]);
});
