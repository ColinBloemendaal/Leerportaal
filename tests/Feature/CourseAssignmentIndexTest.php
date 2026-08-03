<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the filtered assignments index to a reseller-side user', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $course = Course::factory()->create(['title' => 'Fire Safety']);
    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Jane Cursist']);
    CourseAssignment::factory()->for($course)->for($cursist, 'user')->create(['reseller_id' => $reseller->id]);

    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $this->actingAs($user)->get('/admin/reseller/assignments')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reseller/Assignments/Index')
            ->where('assignments.data.0.cursist_name', 'Jane Cursist')
            ->where('assignments.data.0.course_title', 'Fire Safety'));
});

it('denies platform staff from reaching the reseller assignments index', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/reseller/assignments')->assertForbidden();
});
