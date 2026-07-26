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

it('soft deletes a klant and lists it as trashed', function (): void {
    $klant = ResellerKlant::factory()->for($this->reseller, 'reseller')->create();

    $this->actingAs($this->user)
        ->delete("/klanten/{$klant->id}")
        ->assertRedirect('/klanten');

    $this->assertSoftDeleted('resellerklanten', ['id' => $klant->id]);

    $this->actingAs($this->user)
        ->get('/klanten')
        ->assertInertia(fn (AssertableInertia $page) => $page->where('trashed.data.0.id', $klant->id));
});

it('restores a soft-deleted klant', function (): void {
    $klant = ResellerKlant::factory()->for($this->reseller, 'reseller')->create();
    $klant->delete();

    $this->actingAs($this->user)
        ->post("/klanten/{$klant->id}/restore")
        ->assertRedirect('/klanten');

    $this->assertDatabaseHas('resellerklanten', ['id' => $klant->id, 'deleted_at' => null]);
});

it('404s deleting a klant belonging to a different reseller (tenant scope hides it before the policy runs)', function (): void {
    $otherReseller = Reseller::factory()->create();
    $otherKlant = ResellerKlant::factory()->for($otherReseller, 'reseller')->create();

    $this->actingAs($this->user)
        ->delete("/klanten/{$otherKlant->id}")
        ->assertNotFound();
});

it('denies platform staff (no reseller) from viewing klanten', function (): void {
    // 2FA-enabled so the request reaches the policy check being tested,
    // rather than being redirected to two-factor setup first.
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)
        ->get('/klanten')
        ->assertForbidden();
});

it('redirects guests to login', function (): void {
    $this->get('/klanten')->assertRedirect('/login');
});
