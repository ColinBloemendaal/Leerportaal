<?php

declare(strict_types=1);

use App\Enums\QuizType;
use App\Exceptions\InvalidQuizParentException;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('attaches to a lesson as a practice quiz by default', function (): void {
    $quiz = Quiz::factory()->create();

    expect($quiz->lesson_id)->not->toBeNull()
        ->and($quiz->module_id)->toBeNull()
        ->and($quiz->type)->toBe(QuizType::Practice);
});

it('attaches to a module as an exam', function (): void {
    $quiz = Quiz::factory()->forModule()->exam()->create();

    expect($quiz->module_id)->not->toBeNull()
        ->and($quiz->lesson_id)->toBeNull()
        ->and($quiz->type)->toBe(QuizType::Exam);
});

it('rejects a quiz with neither a module nor a lesson', function (): void {
    Quiz::factory()->create(['module_id' => null, 'lesson_id' => null]);
})->throws(InvalidQuizParentException::class);

it('rejects a quiz with both a module and a lesson', function (): void {
    $module = Module::factory()->create();
    $lesson = Lesson::factory()->create();

    Quiz::factory()->create(['module_id' => $module->id, 'lesson_id' => $lesson->id]);
})->throws(InvalidQuizParentException::class);

it('has many questions attached via the reusable question bank pivot', function (): void {
    $quiz = Quiz::factory()->create();
    $questions = Question::factory()->count(3)->create();
    $quiz->questions()->attach($questions->pluck('id'));

    expect($quiz->questions()->count())->toBe(3);
});
