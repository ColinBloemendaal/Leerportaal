<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the filtered resellers index to platform staff', function (): void {
    Reseller::factory()->create(['name' => 'Acme']);
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/platform/resellers?search=acme')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Platform/Resellers/Index')
            ->where('resellers.data.0.name', 'Acme')
            ->where('query.search', 'acme'));
});

it('denies reseller-side users from reaching the resellers index', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/platform/resellers')->assertForbidden();
});
