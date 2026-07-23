<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;

it('renders a 404 as an Inertia error page', function (): void {
    $this->get('/this-route-does-not-exist')
        ->assertNotFound()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Errors/Error')
            ->where('status', 404));
});

it('renders a 403 as an Inertia error page', function (): void {
    Route::get('/__test-403', fn () => abort(403));

    $this->get('/__test-403')
        ->assertForbidden()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Errors/Error')
            ->where('status', 403));
});
