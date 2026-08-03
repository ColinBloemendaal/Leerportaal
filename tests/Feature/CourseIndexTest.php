<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the filtered courses index to a reseller-side user', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    Course::factory()->published()->create(['title' => 'Fire Safety']);

    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($user)->get('/admin/reseller/courses?search=Fire')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reseller/Courses/Index')
            ->where('courses.data.0.title', 'Fire Safety')
            ->where('courses.data.0.is_catalog', true));
});

it('denies platform staff from reaching the reseller courses index', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/reseller/courses')->assertForbidden();
});
