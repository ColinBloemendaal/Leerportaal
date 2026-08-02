<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\FillInBlankQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function fillInBlankQuestion(): FillInBlankQuestion
{
    return new FillInBlankQuestion;
}

function fillInBlankQuestionModel(int $points = 4): Question
{
    return Question::factory()->create([
        'points' => $points,
        'payload' => [
            'template' => 'The capital of {{1}} is {{2}}.',
            'blanks' => [
                ['id' => '1', 'acceptable_answers' => ['France']],
                ['id' => '2', 'acceptable_answers' => ['Paris']],
            ],
        ],
    ]);
}

it('reports its key and label', function (): void {
    expect(FillInBlankQuestion::key())->toBe(QuestionTypeEnum::FillInBlank)
        ->and(fillInBlankQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        ['template' => '{{1}}', 'blanks' => [['id' => '1', 'acceptable_answers' => ['x']]]],
        fillInBlankQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('grades filling every blank correctly as fully correct', function (): void {
    $result = fillInBlankQuestion()->grade(fillInBlankQuestionModel(), ['1' => 'france', '2' => 'paris']);

    expect($result->isCorrect)->toBeTrue()
        ->and($result->pointsAwarded)->toBe(4.0);
});

it('grades filling one of two blanks correctly as partial credit', function (): void {
    $result = fillInBlankQuestion()->grade(fillInBlankQuestionModel(), ['1' => 'France', '2' => 'Berlin']);

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(2.0);
});

it('respects a per-blank case_sensitive flag', function (): void {
    $question = Question::factory()->create([
        'points' => 4,
        'payload' => [
            'template' => '{{1}}',
            'blanks' => [['id' => '1', 'acceptable_answers' => ['Paris'], 'case_sensitive' => true]],
        ],
    ]);

    expect(fillInBlankQuestion()->grade($question, ['1' => 'paris'])->isCorrect)->toBeFalse()
        ->and(fillInBlankQuestion()->grade($question, ['1' => 'Paris'])->isCorrect)->toBeTrue();
});

it('grades a malformed (non-array) answer as incorrect rather than erroring', function (): void {
    $result = fillInBlankQuestion()->grade(fillInBlankQuestionModel(), 'France, Paris');

    expect($result->isCorrect)->toBeFalse();
});

it('grades an empty answer as incorrect', function (): void {
    $result = fillInBlankQuestion()->grade(fillInBlankQuestionModel(), []);

    expect($result->isCorrect)->toBeFalse();
});
