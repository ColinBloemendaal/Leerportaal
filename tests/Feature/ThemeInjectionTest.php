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

it("never leaks reseller A's theme into reseller B's render", function (): void {
    $resellerA = Reseller::factory()->create();
    ResellerTheme::factory()->for($resellerA, 'reseller')->create([
        'primary_color' => '#111111',
        'custom_css' => '.only-a { color: red; }',
    ]);
    $resellerB = Reseller::factory()->create();
    ResellerTheme::factory()->for($resellerB, 'reseller')->create(['primary_color' => '#222222']);

    $this->withCookie('reseller_slug', $resellerB->slug)
        ->get('/')
        ->assertOk()
        ->assertSee('--reseller-primary-color: #222222;', false)
        ->assertDontSee('#111111', false)
        ->assertDontSee('.only-a', false);
});
