<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the filtered activity log to platform staff', function (): void {
    Reseller::factory()->create(['name' => 'Acme']);
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/platform/activity')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Platform/Activity/Index'));
});

it('denies reseller-side users from reaching the activity log', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/platform/activity')->assertForbidden();
});
