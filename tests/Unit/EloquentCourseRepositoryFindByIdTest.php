<?php

declare(strict_types=1);

use App\Models\Course;
use App\Repositories\Eloquent\EloquentCourseRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('finds a course by id with no visibility scoping of its own', function (): void {
    $course = Course::factory()->create();

    $found = app(EloquentCourseRepository::class)->findById($course->id);

    expect($found?->id)->toBe($course->id);
});

it('returns null for a non-existent course id', function (): void {
    expect(app(EloquentCourseRepository::class)->findById(999999))->toBeNull();
});
