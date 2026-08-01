<?php

declare(strict_types=1);

use App\Contracts\Repositories\ResellerThemeRepository;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('finds a specific reseller theme by id without needing ambient tenant context', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();
    app(TenantContext::class)->set($resellerA);
    $themeA = ResellerTheme::factory()->for($resellerA, 'reseller')->create();
    app(TenantContext::class)->set($resellerB);
    ResellerTheme::factory()->for($resellerB, 'reseller')->create();

    // Ambient context is B, but we explicitly ask for A's theme -- this is
    // the whole point of the method (queue workers have no ambient
    // context at all), so it must not be scoped by whatever's ambient.
    $found = app(ResellerThemeRepository::class)->findForReseller($resellerA->id);

    expect($found?->id)->toBe($themeA->id);
});

it('returns null for a reseller that has no theme row', function (): void {
    $reseller = Reseller::factory()->create();

    expect(app(ResellerThemeRepository::class)->findForReseller($reseller->id))->toBeNull();
});
