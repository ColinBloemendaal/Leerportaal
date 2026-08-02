<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the klant dashboard to a klant-admin for their own klant', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $klantAdmin = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    $klantAdmin->assignRole('klant-admin');
    User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $this->actingAs($klantAdmin)->get('/admin/klant')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Klant/Dashboard')
            ->where('stats.cursistCount', 2));
});

it('denies a plain cursist from reaching their klant\'s dashboard', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    $this->actingAs($cursist)->get('/admin/klant')->assertForbidden();
});

it('404s for a user with no resellerklant_id at all', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $staff = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => null]);

    $this->actingAs($staff)->get('/admin/klant')->assertNotFound();
});
