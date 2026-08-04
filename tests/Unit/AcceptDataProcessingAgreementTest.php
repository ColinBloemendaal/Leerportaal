<?php

declare(strict_types=1);

use App\Actions\Gdpr\AcceptDataProcessingAgreement;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('stamps the current DPA version, timestamp, and accepting user', function (): void {
    config(['gdpr.dpa_version' => '2026-08-04']);
    $reseller = Reseller::factory()->create();
    $admin = User::factory()->create(['reseller_id' => $reseller->id]);

    $accepted = app(AcceptDataProcessingAgreement::class)($reseller, $admin);

    expect($accepted->dpa_accepted_version)->toBe('2026-08-04')
        ->and($accepted->dpa_accepted_at)->not->toBeNull()
        ->and($accepted->dpa_accepted_by_user_id)->toBe($admin->id)
        ->and($accepted->hasAcceptedCurrentDpa())->toBeTrue();
});

it('makes a reseller stale again once the DPA version changes', function (): void {
    config(['gdpr.dpa_version' => '2026-08-04']);
    $reseller = Reseller::factory()->create();
    $admin = User::factory()->create(['reseller_id' => $reseller->id]);

    app(AcceptDataProcessingAgreement::class)($reseller, $admin);

    config(['gdpr.dpa_version' => '2027-01-01']);

    expect($reseller->fresh()->hasAcceptedCurrentDpa())->toBeFalse();
});
