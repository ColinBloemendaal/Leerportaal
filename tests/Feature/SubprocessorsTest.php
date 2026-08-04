<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the current sub-processor list to any authenticated user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/subprocessors')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Settings/Subprocessors')
            ->where('document', fn (string $document): bool => str_contains($document, 'Mollie')));
});

it('shows the list to platform staff too', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/settings/subprocessors')->assertOk();
});

it('redirects guests to login', function (): void {
    $this->get('/settings/subprocessors')->assertRedirect('/login');
});
