<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the reseller billing dashboard to a reseller-side user', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    Invoice::factory()->for($reseller)->create(['subtotal_cents' => 1500]);

    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($user)->get('/admin/reseller/billing')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reseller/Billing/Dashboard')
            ->where('stats.currentPeriodSubtotal.cents', 1500));
});

it('denies platform staff from reaching the reseller billing dashboard', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/reseller/billing')->assertForbidden();
});
