<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Laravel\Horizon\Contracts\WorkloadRepository;

it('shows platform health to platform staff', function (): void {
    $fakeWorkload = new class implements WorkloadRepository
    {
        public function get()
        {
            return [];
        }
    };
    app()->instance(WorkloadRepository::class, $fakeWorkload);

    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/platform/health')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Platform/Health/Index')
            ->has('health.failedJobCount'));
});

it('denies reseller-side users from reaching the platform health page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/platform/health')->assertForbidden();
});
