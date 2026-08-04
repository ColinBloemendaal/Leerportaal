<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the filtered invoices index to a reseller-side user', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    Invoice::factory()->for($reseller)->issued()->create(['total_cents' => 4500]);

    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($user)->get('/admin/reseller/invoices')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reseller/Invoices/Index')
            ->where('invoices.data.0.total_cents', 4500));
});

it('denies platform staff from reaching the reseller invoices index', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/reseller/invoices')->assertForbidden();
});
