<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\MultipleResponseQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function multipleResponseQuestion(): MultipleResponseQuestion
{
    return new MultipleResponseQuestion;
}

function multipleResponseQuestionModel(int $points = 6): Question
{
    return Question::factory()->create([
        'points' => $points,
        'payload' => [
            'options' => [
                ['id' => 'a', 'text' => 'Paris'],
                ['id' => 'b', 'text' => 'Lyon'],
                ['id' => 'c', 'text' => 'Berlin'],
            ],
            'correct_option_ids' => ['a', 'b'],
        ],
    ]);
}

it('reports its key and label', function (): void {
    expect(MultipleResponseQuestion::key())->toBe(QuestionTypeEnum::MultipleResponse)
        ->and(multipleResponseQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        [
            'options' => [['id' => 'a', 'text' => 'Paris'], ['id' => 'b', 'text' => 'Berlin']],
            'correct_option_ids' => ['a'],
        ],
        multipleResponseQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('grades selecting exactly the correct options as fully correct', function (): void {
    $result = multipleResponseQuestion()->grade(multipleResponseQuestionModel(), ['a', 'b']);

    expect($result->isCorrect)->toBeTrue()
        ->and($result->pointsAwarded)->toBe(6.0);
});

it('grades selecting only one of two correct options as partial credit', function (): void {
    $result = multipleResponseQuestion()->grade(multipleResponseQuestionModel(), ['a']);

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(3.0);
});

it('grades a correct plus an incorrect selection as reduced partial credit', function (): void {
    $result = multipleResponseQuestion()->grade(multipleResponseQuestionModel(), ['a', 'c']);

    // +3 for 'a' (correct), -3 for 'c' (incorrect) = 0
    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(0.0);
});

it('clamps score to zero rather than going negative', function (): void {
    $result = multipleResponseQuestion()->grade(multipleResponseQuestionModel(), ['c']);

    expect($result->pointsAwarded)->toBe(0.0);
});

it('grades an empty selection as incorrect', function (): void {
    $result = multipleResponseQuestion()->grade(multipleResponseQuestionModel(), []);

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(0.0);
});

it('grades a malformed (non-array) answer as incorrect rather than erroring', function (): void {
    $result = multipleResponseQuestion()->grade(multipleResponseQuestionModel(), 'a');

    expect($result->isCorrect)->toBeFalse();
});
