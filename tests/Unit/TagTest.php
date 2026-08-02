<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('attaches tags to a course', function (): void {
    $course = Course::factory()->create();
    $tag = Tag::factory()->create(['name' => 'javascript']);

    $course->tags()->attach($tag);

    expect($course->tags()->pluck('name'))->toContain('javascript')
        ->and($tag->courses()->pluck('courses.id'))->toContain($course->id);
});

it('allows the same tag to be shared across courses owned by different resellers', function (): void {
    $tag = Tag::factory()->create();
    $platformCourse = Course::factory()->create();
    $resellerCourse = Course::factory()->forReseller()->create();

    $tag->courses()->attach([$platformCourse->id, $resellerCourse->id]);

    expect($tag->courses()->count())->toBe(2);
});
