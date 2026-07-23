<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia;

it('renders the welcome page', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Welcome'));
});
