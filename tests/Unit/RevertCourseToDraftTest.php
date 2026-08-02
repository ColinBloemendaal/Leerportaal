<?php

declare(strict_types=1);

use App\Actions\Courses\RevertCourseToDraft;
use App\Enums\CourseStatus;
use App\Exceptions\InvalidCourseStatusTransitionException;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('reverts an in-review course to draft', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::InReview]);

    $result = (new RevertCourseToDraft)($course);

    expect($result->status)->toBe(CourseStatus::Draft)
        ->and($course->fresh()?->status)->toBe(CourseStatus::Draft);
});

it('reverts a published course to draft', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::Published]);

    $result = (new RevertCourseToDraft)($course);

    expect($result->status)->toBe(CourseStatus::Draft);
});

it('rejects reverting a course that is already a draft', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::Draft]);

    expect(fn () => (new RevertCourseToDraft)($course))
        ->toThrow(InvalidCourseStatusTransitionException::class);
});
