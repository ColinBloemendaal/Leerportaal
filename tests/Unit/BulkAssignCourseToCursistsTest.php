<?php

declare(strict_types=1);

use App\Actions\Courses\BulkAssignCourseToCursists;
use App\Models\Course;
use App\Models\Reseller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    app(TenantContext::class)->set(Reseller::factory()->create());
});

it('assigns a course to every given user', function (): void {
    $course = Course::factory()->create(['platform_price_cents' => 1000]);
    $admin = User::factory()->create();
    $cursists = User::factory()->count(3)->create();

    $assignments = app(BulkAssignCourseToCursists::class)($course, $cursists->pluck('id')->all(), $admin->id);

    expect($assignments)->toHaveCount(3)
        ->and($course->fresh()->assignments()->count())->toBe(3)
        ->and($assignments->pluck('user_id')->sort()->values()->all())
        ->toBe($cursists->pluck('id')->sort()->values()->all());
});

it('assigns nothing for an empty user list', function (): void {
    $course = Course::factory()->create();
    $admin = User::factory()->create();

    $assignments = app(BulkAssignCourseToCursists::class)($course, [], $admin->id);

    expect($assignments)->toHaveCount(0)
        ->and($course->fresh()->assignments()->count())->toBe(0);
});
