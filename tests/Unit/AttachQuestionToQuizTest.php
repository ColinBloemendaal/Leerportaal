<?php

declare(strict_types=1);

use App\Actions\Questions\AttachQuestionToQuiz;
use App\Actions\Questions\DetachQuestionFromQuiz;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('attaches a bank question to a quiz with an explicit order', function (): void {
    $quiz = Quiz::factory()->create();
    $question = Question::factory()->create();

    app(AttachQuestionToQuiz::class)($quiz, $question, 2);

    expect($quiz->questions()->count())->toBe(1)
        ->and($quiz->questions()->first()->pivot->order)->toBe(2);
});

it('defaults order to the current question count when not given', function (): void {
    $quiz = Quiz::factory()->create();
    $first = Question::factory()->create();
    $second = Question::factory()->create();

    app(AttachQuestionToQuiz::class)($quiz, $first);
    app(AttachQuestionToQuiz::class)($quiz, $second);

    expect($quiz->questions()->find($first->id)->pivot->order)->toBe(0)
        ->and($quiz->questions()->find($second->id)->pivot->order)->toBe(1);
});

it('attaching the same question twice does not duplicate the pivot row', function (): void {
    $quiz = Quiz::factory()->create();
    $question = Question::factory()->create();

    app(AttachQuestionToQuiz::class)($quiz, $question, 0);
    app(AttachQuestionToQuiz::class)($quiz, $question, 0);

    expect($quiz->questions()->count())->toBe(1);
});

it('reuses the same question across two different quizzes', function (): void {
    $quizA = Quiz::factory()->create();
    $quizB = Quiz::factory()->forModule()->create();
    $question = Question::factory()->create();

    app(AttachQuestionToQuiz::class)($quizA, $question);
    app(AttachQuestionToQuiz::class)($quizB, $question);

    expect($question->quizzes()->count())->toBe(2);
});

it('detaches a question from a quiz without affecting other quizzes using it', function (): void {
    $quizA = Quiz::factory()->create();
    $quizB = Quiz::factory()->forModule()->create();
    $question = Question::factory()->create();

    app(AttachQuestionToQuiz::class)($quizA, $question);
    app(AttachQuestionToQuiz::class)($quizB, $question);

    app(DetachQuestionFromQuiz::class)($quizA, $question);

    expect($quizA->questions()->count())->toBe(0)
        ->and($quizB->questions()->count())->toBe(1);
});
