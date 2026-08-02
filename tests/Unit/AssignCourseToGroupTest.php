<?php

declare(strict_types=1);

use App\Actions\Courses\AssignCourseToGroup;
use App\Models\Course;
use App\Models\Group;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    app(TenantContext::class)->set(Reseller::factory()->create());
});

it('assigns a course to every member of a group', function (): void {
    $course = Course::factory()->create();
    $admin = User::factory()->create();
    $group = Group::factory()->create();
    $members = User::factory()->count(3)->create();
    $group->members()->attach($members->pluck('id'));

    $assignments = app(AssignCourseToGroup::class)($course, $group, $admin->id);

    expect($assignments)->toHaveCount(3)
        ->and($assignments->pluck('user_id')->sort()->values()->all())
        ->toBe($members->pluck('id')->sort()->values()->all());
});

it('assigns nothing for an empty group', function (): void {
    $course = Course::factory()->create();
    $admin = User::factory()->create();
    $group = Group::factory()->create();

    $assignments = app(AssignCourseToGroup::class)($course, $group, $admin->id);

    expect($assignments)->toHaveCount(0);
});
