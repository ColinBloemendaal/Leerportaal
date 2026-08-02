<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\NumericQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function numericQuestion(): NumericQuestion
{
    return new NumericQuestion;
}

it('reports its key and label', function (): void {
    expect(NumericQuestion::key())->toBe(QuestionTypeEnum::Numeric)
        ->and(numericQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(['correct_answer' => 3.14, 'tolerance' => 0.01], numericQuestion()->payloadRules());

    expect($validator->passes())->toBeTrue();
});

it('grades an exact match as correct when no tolerance is set', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => 42]]);

    expect(numericQuestion()->grade($question, 42)->isCorrect)->toBeTrue();
});

it('rejects a near miss when no tolerance is set', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => 42]]);

    expect(numericQuestion()->grade($question, 42.1)->isCorrect)->toBeFalse();
});

it('accepts an answer within tolerance', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => 100, 'tolerance' => 5]]);

    expect(numericQuestion()->grade($question, 104)->isCorrect)->toBeTrue()
        ->and(numericQuestion()->grade($question, 96)->isCorrect)->toBeTrue();
});

it('rejects an answer outside tolerance', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => 100, 'tolerance' => 5]]);

    expect(numericQuestion()->grade($question, 106)->isCorrect)->toBeFalse();
});

it('accepts a numeric string answer', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => 42]]);

    expect(numericQuestion()->grade($question, '42')->isCorrect)->toBeTrue();
});

it('grades a malformed (non-numeric) answer as incorrect rather than erroring', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => 42]]);

    expect(numericQuestion()->grade($question, 'forty-two')->isCorrect)->toBeFalse()
        ->and(numericQuestion()->grade($question, null)->isCorrect)->toBeFalse()
        ->and(numericQuestion()->grade($question, [42])->isCorrect)->toBeFalse();
});
