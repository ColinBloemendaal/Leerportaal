<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\MultipleChoiceQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function multipleChoiceQuestion(): MultipleChoiceQuestion
{
    return new MultipleChoiceQuestion;
}

it('reports its key and label', function (): void {
    expect(MultipleChoiceQuestion::key())->toBe(QuestionTypeEnum::MultipleChoice)
        ->and(MultipleChoiceQuestion::label())->toBe('Multiple choice')
        ->and(multipleChoiceQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        [
            'options' => [
                ['id' => 'a', 'text' => 'Paris'],
                ['id' => 'b', 'text' => 'Berlin'],
            ],
            'correct_option_id' => 'a',
        ],
        multipleChoiceQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects a payload with fewer than two options', function (): void {
    $validator = Validator::make(
        ['options' => [['id' => 'a', 'text' => 'Paris']], 'correct_option_id' => 'a'],
        multipleChoiceQuestion()->payloadRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('grades a correct answer', function (): void {
    $question = Question::factory()->create([
        'points' => 4,
        'payload' => [
            'options' => [['id' => 'a', 'text' => 'Paris'], ['id' => 'b', 'text' => 'Berlin']],
            'correct_option_id' => 'a',
        ],
    ]);

    $result = multipleChoiceQuestion()->grade($question, 'a');

    expect($result->isCorrect)->toBeTrue()
        ->and($result->pointsAwarded)->toBe(4.0);
});

it('grades an incorrect answer', function (): void {
    $question = Question::factory()->create([
        'points' => 4,
        'payload' => [
            'options' => [['id' => 'a', 'text' => 'Paris'], ['id' => 'b', 'text' => 'Berlin']],
            'correct_option_id' => 'a',
        ],
    ]);

    $result = multipleChoiceQuestion()->grade($question, 'b');

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(0.0);
});

it('grades a malformed (non-string) answer as incorrect rather than erroring', function (): void {
    $question = Question::factory()->create([
        'points' => 4,
        'payload' => ['options' => [['id' => 'a', 'text' => 'Paris']], 'correct_option_id' => 'a'],
    ]);

    $result = multipleChoiceQuestion()->grade($question, ['a', 'b']);

    expect($result->isCorrect)->toBeFalse();
});

it('grades an empty answer as incorrect', function (): void {
    $question = Question::factory()->create([
        'points' => 4,
        'payload' => ['options' => [['id' => 'a', 'text' => 'Paris']], 'correct_option_id' => 'a'],
    ]);

    $result = multipleChoiceQuestion()->grade($question, null);

    expect($result->isCorrect)->toBeFalse();
});
