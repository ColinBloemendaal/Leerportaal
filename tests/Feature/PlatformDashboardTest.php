<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the platform dashboard to platform staff with aggregate stats', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    // Explicit user: CourseAssignmentFactory defaults user_id to its own
    // User::factory(), which would implicitly create a second Reseller
    // (User::factory()'s own default reseller_id) and inflate the
    // resellerCount assertion below.
    CourseAssignment::factory()->for($course)->for($cursist, 'user')->create(['reseller_id' => $reseller->id, 'price_cents' => 1000]);

    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/platform')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Platform/Dashboard')
            ->where('stats.resellerCount', 1)
            ->where('stats.courseCount', 1));
});

it('denies reseller-side users from reaching the platform dashboard', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($user)->get('/admin/platform')->assertForbidden();
});
