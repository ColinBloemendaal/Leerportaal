<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\User;
use App\Policies\QuizPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new QuizPolicy;
});

it('allows a user with a live assignment for the quiz\'s course', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $quiz = Quiz::factory()->forModule($module->id)->create();
    $user = User::factory()->create();
    CourseAssignment::factory()->for($course)->for($user, 'user')->create();

    expect($this->policy->start($user, $quiz))->toBeTrue();
});

it('denies a user with no assignment at all', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $quiz = Quiz::factory()->forModule($module->id)->create();
    $user = User::factory()->create();

    expect($this->policy->start($user, $quiz))->toBeFalse();
});

it('denies a user whose only assignment for that course was revoked', function (): void {
    $course = Course::factory()->create();
    $module = Module::factory()->for($course)->create();
    $quiz = Quiz::factory()->forModule($module->id)->create();
    $user = User::factory()->create();
    CourseAssignment::factory()->for($course)->for($user, 'user')->revoked()->create();

    expect($this->policy->start($user, $quiz))->toBeFalse();
});
