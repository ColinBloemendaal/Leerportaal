<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;
use App\Repositories\Eloquent\EloquentCourseAssignmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('finds every assignment for a user, including revoked ones, with no ambient tenant', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);

    $active = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursist, 'user')->create();
    $revoked = CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->for($cursist, 'user')->revoked()->create();
    CourseAssignment::factory()->for($reseller, 'reseller')->for($course)->create(); // someone else's

    $found = app(EloquentCourseAssignmentRepository::class)->allForUser($cursist->id);

    expect($found->pluck('id')->sort()->values()->all())->toBe(collect([$active->id, $revoked->id])->sort()->values()->all());
});
