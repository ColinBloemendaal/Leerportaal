<?php

declare(strict_types=1);

use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\Group;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    $this->reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($this->reseller);
    $this->admin = User::factory()->create(['reseller_id' => $this->reseller->id]);
    $this->klant = ResellerKlant::factory()->for($this->reseller)->create();
    $this->course = Course::factory()->create(['title' => 'Fire Safety']);
});

it('shows the create page with courses, cursisten, and groups scoped to the current reseller', function (): void {
    User::factory()->create(['reseller_id' => $this->reseller->id, 'resellerklant_id' => $this->klant->id, 'name' => 'Jane Cursist']);
    Group::factory()->for($this->reseller)->create(['name' => 'Team A']);
    User::factory()->create(); // a different reseller's cursist, must not appear

    $this->actingAs($this->admin)->get('/admin/reseller/assignments/create')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Reseller/Assignments/Create')
            ->where('courses.data.0.title', 'Fire Safety')
            ->has('cursisten.data', 1)
            ->where('cursisten.data.0.name', 'Jane Cursist')
            ->has('groups.data', 1)
            ->where('groups.data.0.name', 'Team A'));
});

it('assigns a course to one cursist', function (): void {
    $cursist = User::factory()->create(['reseller_id' => $this->reseller->id, 'resellerklant_id' => $this->klant->id]);

    $this->actingAs($this->admin)
        ->post('/admin/reseller/assignments', ['course_id' => $this->course->id, 'user_id' => $cursist->id])
        ->assertRedirect('/admin/reseller/assignments');

    $this->assertDatabaseHas('course_assignments', [
        'course_id' => $this->course->id,
        'user_id' => $cursist->id,
        // Billed immediately, not Pending -- CLAUDE.md §11's billable
        // event fires at assignment time (RecordBillableAssignment),
        // not deferred to a later step.
        'billing_state' => AssignmentBillingState::Billed->value,
    ]);
});

it('assigns a course to multiple cursisten in bulk', function (): void {
    $cursistA = User::factory()->create(['reseller_id' => $this->reseller->id, 'resellerklant_id' => $this->klant->id]);
    $cursistB = User::factory()->create(['reseller_id' => $this->reseller->id, 'resellerklant_id' => $this->klant->id]);

    $this->actingAs($this->admin)
        ->post('/admin/reseller/assignments/bulk', ['course_id' => $this->course->id, 'user_ids' => [$cursistA->id, $cursistB->id]])
        ->assertRedirect('/admin/reseller/assignments');

    $this->assertDatabaseHas('course_assignments', ['course_id' => $this->course->id, 'user_id' => $cursistA->id]);
    $this->assertDatabaseHas('course_assignments', ['course_id' => $this->course->id, 'user_id' => $cursistB->id]);
});

it('assigns a course to every member of a group', function (): void {
    $group = Group::factory()->for($this->reseller)->create();
    $member = User::factory()->create(['reseller_id' => $this->reseller->id, 'resellerklant_id' => $this->klant->id]);
    $group->members()->attach($member);

    $this->actingAs($this->admin)
        ->post('/admin/reseller/assignments/group', ['course_id' => $this->course->id, 'group_id' => $group->id])
        ->assertRedirect('/admin/reseller/assignments');

    $this->assertDatabaseHas('course_assignments', ['course_id' => $this->course->id, 'user_id' => $member->id]);
});

it('rejects assigning to a cursist from a different reseller', function (): void {
    $otherCursist = User::factory()->create();

    $this->actingAs($this->admin)
        ->post('/admin/reseller/assignments', ['course_id' => $this->course->id, 'user_id' => $otherCursist->id])
        ->assertInvalid(['user_id']);
});

it('rejects assigning a group from a different reseller', function (): void {
    $otherGroup = Group::factory()->create();

    $this->actingAs($this->admin)
        ->post('/admin/reseller/assignments/group', ['course_id' => $this->course->id, 'group_id' => $otherGroup->id])
        ->assertInvalid(['group_id']);
});

it('denies platform staff from reaching the create page', function (): void {
    $staff = User::factory()->platformStaff()->twoFactorEnabled()->create();

    $this->actingAs($staff)->get('/admin/reseller/assignments/create')->assertForbidden();
});
