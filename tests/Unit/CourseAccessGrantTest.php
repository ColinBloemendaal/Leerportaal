<?php

declare(strict_types=1);

use App\Exceptions\InvalidCourseAccessGrantException;
use App\Models\Course;
use App\Models\CourseAccessGrant;
use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('grants access to a specific course by default', function (): void {
    $grant = CourseAccessGrant::factory()->create();

    expect($grant->course_id)->not->toBeNull()
        ->and($grant->course_category_id)->toBeNull();
});

it('grants access to a whole category', function (): void {
    $grant = CourseAccessGrant::factory()->forCategory()->create();

    expect($grant->course_category_id)->not->toBeNull()
        ->and($grant->course_id)->toBeNull();
});

it('rejects a grant with neither a course nor a category', function (): void {
    CourseAccessGrant::factory()->create(['course_id' => null, 'course_category_id' => null]);
})->throws(InvalidCourseAccessGrantException::class);

it('rejects a grant with both a course and a category', function (): void {
    $course = Course::factory()->create();
    $category = CourseCategory::factory()->create();

    CourseAccessGrant::factory()->create(['course_id' => $course->id, 'course_category_id' => $category->id]);
})->throws(InvalidCourseAccessGrantException::class);

it('reports whether it has been revoked', function (): void {
    $active = CourseAccessGrant::factory()->create();
    $revoked = CourseAccessGrant::factory()->revoked()->create();

    expect($active->isRevoked())->toBeFalse()
        ->and($revoked->isRevoked())->toBeTrue();
});
