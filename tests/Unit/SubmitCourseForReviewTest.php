<?php

declare(strict_types=1);

use App\Actions\Courses\SubmitCourseForReview;
use App\Enums\CourseStatus;
use App\Exceptions\InvalidCourseStatusTransitionException;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('moves a draft course to in review', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::Draft]);

    $result = (new SubmitCourseForReview)($course);

    expect($result->status)->toBe(CourseStatus::InReview)
        ->and($course->fresh()?->status)->toBe(CourseStatus::InReview);
});

it('rejects submitting a course that is already in review', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::InReview]);

    expect(fn () => (new SubmitCourseForReview)($course))
        ->toThrow(InvalidCourseStatusTransitionException::class);
});

it('rejects submitting a published course', function (): void {
    $course = Course::factory()->create(['status' => CourseStatus::Published]);

    expect(fn () => (new SubmitCourseForReview)($course))
        ->toThrow(InvalidCourseStatusTransitionException::class);
});
