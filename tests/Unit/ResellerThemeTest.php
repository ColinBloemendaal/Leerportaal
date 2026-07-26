<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('defaults to a sensible primary color when not customized', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $theme = ResellerTheme::create(['reseller_id' => $reseller->id]);
    $theme = $theme->fresh() ?? $theme;

    expect($theme->primary_color)->toBe('#0d6efd')
        ->and($theme->secondary_color)->toBeNull()
        ->and($theme->accent_color)->toBeNull();
});

it('belongs to a reseller', function (): void {
    $theme = ResellerTheme::factory()->create();

    expect($theme->reseller)->toBeInstanceOf(Reseller::class);
});

it('soft deletes', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $theme = ResellerTheme::factory()->for($reseller, 'reseller')->create();

    $theme->delete();

    expect(ResellerTheme::find($theme->id))->toBeNull()
        ->and(ResellerTheme::withTrashed()->find($theme->id))->not->toBeNull();
});
