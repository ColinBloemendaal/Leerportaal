<?php

declare(strict_types=1);

use App\Actions\Courses\AddCoursePrerequisite;
use App\Exceptions\CircularCoursePrerequisiteException;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('adds a course as a prerequisite of another', function (): void {
    $course = Course::factory()->create();
    $prerequisite = Course::factory()->create();

    app(AddCoursePrerequisite::class)($course, $prerequisite);

    expect($course->prerequisites()->pluck('courses.id'))->toContain($prerequisite->id);
});

it('rejects a course as its own prerequisite', function (): void {
    $course = Course::factory()->create();

    app(AddCoursePrerequisite::class)($course, $course);
})->throws(CircularCoursePrerequisiteException::class);

it('rejects a direct circular prerequisite', function (): void {
    $a = Course::factory()->create();
    $b = Course::factory()->create();

    app(AddCoursePrerequisite::class)($a, $b);

    app(AddCoursePrerequisite::class)($b, $a);
})->throws(CircularCoursePrerequisiteException::class);

it('rejects a transitive circular prerequisite', function (): void {
    $a = Course::factory()->create();
    $b = Course::factory()->create();
    $c = Course::factory()->create();

    app(AddCoursePrerequisite::class)($a, $b);
    app(AddCoursePrerequisite::class)($b, $c);

    app(AddCoursePrerequisite::class)($c, $a);
})->throws(CircularCoursePrerequisiteException::class);

it('does not duplicate an existing prerequisite link', function (): void {
    $course = Course::factory()->create();
    $prerequisite = Course::factory()->create();

    app(AddCoursePrerequisite::class)($course, $prerequisite);
    app(AddCoursePrerequisite::class)($course, $prerequisite);

    expect($course->prerequisites()->count())->toBe(1);
});
