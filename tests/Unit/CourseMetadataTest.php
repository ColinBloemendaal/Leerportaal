<?php

declare(strict_types=1);

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('stores an estimated duration in minutes', function (): void {
    $course = Course::factory()->create(['estimated_duration_minutes' => 90]);

    expect($course->fresh()->estimated_duration_minutes)->toBe(90);
});

it('stores learning objectives as a plain list, not per locale', function (): void {
    $objectives = ['Understand tenancy scoping', 'Write an isolation test'];
    $course = Course::factory()->create(['learning_objectives' => $objectives]);

    expect($course->fresh()->learning_objectives)->toBe($objectives);
});

it('leaves duration and objectives null by default', function (): void {
    $course = Course::factory()->create();

    expect($course->estimated_duration_minutes)->toBeNull()
        ->and($course->learning_objectives)->toBeNull();
});
