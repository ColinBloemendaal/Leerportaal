<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerTheme;

it('injects the reseller theme as CSS custom properties on a branded request', function (): void {
    $reseller = Reseller::factory()->create();
    ResellerTheme::factory()->for($reseller, 'reseller')->create(['primary_color' => '#123abc']);

    $this->withCookie('reseller_slug', $reseller->slug)
        ->get('/')
        ->assertOk()
        ->assertSee('--reseller-primary-color: #123abc;', false);
});

it('renders no theme style block on the unbranded fallback', function (): void {
    $response = $this->get('/')->assertOk();

    $response->assertDontSee('--reseller-primary-color', false);
});

it('renders no theme style block for a reseller with no theme row yet', function (): void {
    $reseller = Reseller::factory()->create();

    $this->withCookie('reseller_slug', $reseller->slug)
        ->get('/')
        ->assertOk()
        ->assertDontSee('--reseller-primary-color', false);
});
