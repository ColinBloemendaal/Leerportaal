<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Reseller;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows a user\'s detail page with their timeline to platform staff', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme']);
    $target = User::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Jane Doe']);
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get("/admin/platform/users/{$target->id}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Platform/Users/Show')
            ->where('user.data.name', 'Jane Doe')
            ->where('user.data.reseller_name', 'Acme')
            // A plain platform staffer (no special role) can't impersonate --
            // only a super-admin can, per UserPolicy::impersonate().
            ->where('canImpersonate', false));
});

it('offers the impersonation entry point to a super-admin viewing a reseller-side user', function (): void {
    $target = User::factory()->create();
    $superAdmin = User::factory()->platformRole(Role::SuperAdmin)->twoFactorEnabled()->create();

    $this->actingAs($superAdmin)->get("/admin/platform/users/{$target->id}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->where('canImpersonate', true));
});

it('lets a super-admin actually start impersonating from the detail page', function (): void {
    $target = User::factory()->create();
    $superAdmin = User::factory()->platformRole(Role::SuperAdmin)->twoFactorEnabled()->create();

    $this->actingAs($superAdmin)
        ->post("/impersonate/{$target->id}", ['reason' => 'Investigating a support ticket'])
        ->assertRedirect('/');

    $this->assertDatabaseHas('impersonations', [
        'impersonator_user_id' => $superAdmin->id,
        'impersonated_user_id' => $target->id,
    ]);
});

it('404s for a non-existent user', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/platform/users/999999')->assertNotFound();
});

it('denies reseller-side users from reaching the user detail page', function (): void {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)->get("/admin/platform/users/{$target->id}")->assertForbidden();
});
