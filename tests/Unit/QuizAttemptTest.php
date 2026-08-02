<?php

declare(strict_types=1);

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a quiz and a user', function (): void {
    $quiz = Quiz::factory()->create();
    $user = User::factory()->create();
    $attempt = QuizAttempt::factory()->for($quiz)->for($user)->create();

    expect($attempt->quiz->is($quiz))->toBeTrue()
        ->and($attempt->user->is($user))->toBeTrue();
});

it('enforces one attempt number per user per quiz', function (): void {
    $quiz = Quiz::factory()->create();
    $user = User::factory()->create();

    QuizAttempt::factory()->for($quiz)->for($user)->create(['attempt_number' => 1]);
    QuizAttempt::factory()->for($quiz)->for($user)->create(['attempt_number' => 1]);
})->throws(QueryException::class);

it('allows the same user multiple distinct attempt numbers on the same quiz', function (): void {
    $quiz = Quiz::factory()->create();
    $user = User::factory()->create();

    QuizAttempt::factory()->for($quiz)->for($user)->create(['attempt_number' => 1]);
    QuizAttempt::factory()->for($quiz)->for($user)->create(['attempt_number' => 2]);

    expect($quiz->attempts()->where('user_id', $user->id)->count())->toBe(2);
});

it('has many question answers, retained after grading', function (): void {
    $attempt = QuizAttempt::factory()->submitted()->create();
    $question = Question::factory()->create();

    QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for($question)->graded(true, 2.0, 2.0)->create([
        'answer' => ['selected' => 'a'],
    ]);

    $answer = $attempt->answers()->first();

    expect($answer->answer)->toBe(['selected' => 'a'])
        ->and($attempt->answers()->count())->toBe(1);
});

it('prevents two answers for the same question within one attempt', function (): void {
    $attempt = QuizAttempt::factory()->create();
    $question = Question::factory()->create();

    QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for($question)->create();
    QuestionAnswer::factory()->for($attempt, 'quizAttempt')->for($question)->create();
})->throws(QueryException::class);
