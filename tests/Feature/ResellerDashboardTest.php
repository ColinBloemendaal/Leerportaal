<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the reseller dashboard to a reseller-side user with aggregate stats', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);

    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($user)->get('/admin/reseller')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reseller/Dashboard')
            ->where('stats.klantCount', 1));
});

it('denies platform staff from reaching the reseller dashboard', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/reseller')->assertForbidden();
});
