<?php

declare(strict_types=1);

use App\Models\Reseller;

it('sets the reseller cookie and redirects to login for an active reseller', function (): void {
    $reseller = Reseller::factory()->create(['slug' => 'acme']);

    $response = $this->get('/login/acme');

    $response->assertRedirect('/login');
    $response->assertCookie('reseller_slug', $reseller->slug);
});

it('404s for an unknown slug', function (): void {
    $this->get('/login/does-not-exist')->assertNotFound();
});

it('404s for a suspended reseller', function (): void {
    Reseller::factory()->suspended()->create(['slug' => 'suspended-co']);

    $this->get('/login/suspended-co')->assertNotFound();
});
