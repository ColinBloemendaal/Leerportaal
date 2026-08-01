<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerTheme;
use Inertia\Testing\AssertableInertia;

it('shares empty footer props on the unbranded fallback', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('footer.text', null)
            ->where('footer.supportEmail', null)
            ->where('footer.termsUrl', null)
            ->where('footer.privacyUrl', null));
});

it('shares the current reseller\'s footer content as Inertia props', function (): void {
    $reseller = Reseller::factory()->create();
    ResellerTheme::factory()->for($reseller, 'reseller')->create([
        'footer_text' => '© Acme Training.',
        'support_email' => 'support@acme.example',
        'terms_url' => 'https://acme.example/terms',
        'privacy_url' => 'https://acme.example/privacy',
    ]);

    $this->withCookie('reseller_slug', $reseller->slug)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('footer.text', '© Acme Training.')
            ->where('footer.supportEmail', 'support@acme.example')
            ->where('footer.termsUrl', 'https://acme.example/terms')
            ->where('footer.privacyUrl', 'https://acme.example/privacy'));
});

it("never leaks reseller A's footer content into reseller B's render", function (): void {
    $resellerA = Reseller::factory()->create();
    ResellerTheme::factory()->for($resellerA, 'reseller')->create(['footer_text' => 'Only for A']);
    $resellerB = Reseller::factory()->create();
    ResellerTheme::factory()->for($resellerB, 'reseller')->create(['footer_text' => 'Only for B']);

    $this->withCookie('reseller_slug', $resellerB->slug)
        ->get('/')
        ->assertInertia(fn (AssertableInertia $page) => $page->where('footer.text', 'Only for B'));
});
