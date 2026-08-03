<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia;

it('renders the unbranded fallback experience with no tenant resolved', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Welcome')
            ->where('appName', config('app.name'))
            ->where('locale', config('app.locale')));
});

it('renders the same unbranded page for an unresolvable reseller_slug cookie', function (): void {
    $this->withCookie('reseller_slug', 'does-not-exist')
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Welcome'));
});
