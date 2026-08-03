<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the filtered users index to platform staff', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme']);
    User::factory()->create(['name' => 'Jane Doe', 'reseller_id' => $reseller->id]);
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/platform/users?search=Jane')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Platform/Users/Index')
            ->where('users.data.0.name', 'Jane Doe')
            ->where('users.data.0.reseller_name', 'Acme'));
});

it('denies reseller-side users from reaching the platform users index', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/platform/users')->assertForbidden();
});
