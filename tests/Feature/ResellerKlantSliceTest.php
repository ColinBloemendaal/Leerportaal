<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    $this->reseller = Reseller::factory()->create();
    $this->user = User::factory()->create(['reseller_id' => $this->reseller->id]);
    app(TenantContext::class)->set($this->reseller);
});

it('lists klanten for the current reseller', function (): void {
    ResellerKlant::factory()->for($this->reseller, 'reseller')->create(['name' => 'Acme BV']);

    $this->actingAs($this->user)
        ->get('/klanten')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Klanten/Index')
            ->where('klanten.data.0.name', 'Acme BV'));
});

it('creates a klant through the full FormRequest -> DTO -> Action -> Repository chain', function (): void {
    $this->actingAs($this->user)
        ->post('/klanten', ['name' => 'Nieuwe Klant'])
        ->assertRedirect('/klanten');

    $this->assertDatabaseHas('resellerklanten', [
        'name' => 'Nieuwe Klant',
        'reseller_id' => $this->reseller->id,
    ]);
});

it('rejects an empty name', function (): void {
    $this->actingAs($this->user)
        ->post('/klanten', ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('denies platform staff (no reseller) from viewing klanten', function (): void {
    $staff = User::factory()->platformStaff()->create();

    $this->actingAs($staff)
        ->get('/klanten')
        ->assertForbidden();
});

it('denies guests', function (): void {
    $this->get('/klanten')->assertForbidden();
});
