<?php

declare(strict_types=1);

use App\Actions\Courses\AddCoursePrerequisite;
use App\Actions\Courses\RemoveCoursePrerequisite;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('removes a prerequisite link', function (): void {
    $course = Course::factory()->create();
    $prerequisite = Course::factory()->create();

    app(AddCoursePrerequisite::class)($course, $prerequisite);
    app(RemoveCoursePrerequisite::class)($course, $prerequisite);

    expect($course->prerequisites()->pluck('courses.id'))->not->toContain($prerequisite->id);
});

it('is a no-op when the link does not exist', function (): void {
    $course = Course::factory()->create();
    $prerequisite = Course::factory()->create();

    app(RemoveCoursePrerequisite::class)($course, $prerequisite);

    expect($course->prerequisites()->count())->toBe(0);
});
