<?php

declare(strict_types=1);

use App\Actions\Progress\MarkBlockCompleted;
use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Inertia\Testing\AssertableInertia;

it('shows the dashboard for an authenticated user with no assignments', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('assignments', []));
});

it('shows an assigned course as not started', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->create();
    $assignment = CourseAssignment::factory()->for($course)->for($user, 'user')->create(['reseller_id' => $reseller->id]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('assignments.0.assignmentId', $assignment->id)
            ->where('assignments.0.status', 'not_started'));
});

it('shows a completed assignment as completed', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $lesson = Lesson::factory()->for($module)->create();
    $block = Block::factory()->for($lesson)->create();
    $assignment = CourseAssignment::factory()->for($course)->for($user, 'user')->create(['reseller_id' => $reseller->id]);
    app(MarkBlockCompleted::class)($assignment, $block);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('assignments.0.status', 'completed')
            ->where('assignments.0.progressPercent', 100));
});
