<?php

declare(strict_types=1);

use App\Actions\Quizzes\StartQuizAttempt;
use App\Actions\Quizzes\SubmitQuizAttempt;
use App\Enums\QuestionTypeEnum;
use App\Exceptions\QuizAttemptNotAllowedException;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('writes submitted answers into the frozen rows, grades, and stamps submitted_at', function (): void {
    $quiz = Quiz::factory()->exam()->create();
    $question = Question::factory()->create([
        'type' => QuestionTypeEnum::TrueFalse,
        'points' => 10,
        'payload' => ['correct_answer' => true],
    ]);
    $quiz->questions()->attach($question->id, ['order' => 0]);
    $user = User::factory()->create();

    $attempt = app(StartQuizAttempt::class)($quiz, $user);

    $graded = app(SubmitQuizAttempt::class)($attempt, [$question->id => true]);

    expect($graded->submitted_at)->not->toBeNull()
        ->and($graded->score)->toBe(10.0)
        ->and($graded->max_score)->toBe(10.0)
        ->and($graded->answers->first()->answer)->toBeTrue()
        ->and($graded->answers->first()->is_correct)->toBeTrue();
});

it('rejects submitting the same attempt twice', function (): void {
    $quiz = Quiz::factory()->exam()->create();
    $question = Question::factory()->create(['type' => QuestionTypeEnum::TrueFalse, 'payload' => ['correct_answer' => true]]);
    $quiz->questions()->attach($question->id, ['order' => 0]);
    $user = User::factory()->create();

    $attempt = app(StartQuizAttempt::class)($quiz, $user);
    app(SubmitQuizAttempt::class)($attempt, [$question->id => true]);

    expect(fn () => app(SubmitQuizAttempt::class)($attempt->fresh(), [$question->id => false]))
        ->toThrow(QuizAttemptNotAllowedException::class);
});

it('leaves an unanswered question\'s answer null', function (): void {
    $quiz = Quiz::factory()->exam()->create();
    $questionA = Question::factory()->create(['type' => QuestionTypeEnum::TrueFalse, 'payload' => ['correct_answer' => true]]);
    $questionB = Question::factory()->create(['type' => QuestionTypeEnum::TrueFalse, 'payload' => ['correct_answer' => false]]);
    $quiz->questions()->attach($questionA->id, ['order' => 0]);
    $quiz->questions()->attach($questionB->id, ['order' => 1]);
    $user = User::factory()->create();

    $attempt = app(StartQuizAttempt::class)($quiz, $user);

    $graded = app(SubmitQuizAttempt::class)($attempt, [$questionA->id => true]);

    $unanswered = $graded->answers->firstWhere('question_id', $questionB->id);
    expect($unanswered->answer)->toBeNull()
        ->and($unanswered->is_correct)->toBeFalse();
});
