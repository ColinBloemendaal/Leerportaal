<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\TrueFalseQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function trueFalseQuestion(): TrueFalseQuestion
{
    return new TrueFalseQuestion;
}

it('reports its key and label', function (): void {
    expect(TrueFalseQuestion::key())->toBe(QuestionTypeEnum::TrueFalse)
        ->and(trueFalseQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(['correct_answer' => true], trueFalseQuestion()->payloadRules());

    expect($validator->passes())->toBeTrue();
});

it('rejects a non-boolean correct_answer', function (): void {
    $validator = Validator::make(['correct_answer' => 'yes please'], trueFalseQuestion()->payloadRules());

    expect($validator->fails())->toBeTrue();
});

it('grades a correct true answer', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => true]]);

    $result = trueFalseQuestion()->grade($question, true);

    expect($result->isCorrect)->toBeTrue()
        ->and($result->pointsAwarded)->toBe(2.0);
});

it('grades an incorrect false answer', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => true]]);

    $result = trueFalseQuestion()->grade($question, false);

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(0.0);
});

it('grades a malformed (non-boolean) answer as incorrect rather than erroring', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => true]]);

    $result = trueFalseQuestion()->grade($question, 'true');

    expect($result->isCorrect)->toBeFalse();
});

it('grades an empty answer as incorrect', function (): void {
    $question = Question::factory()->create(['points' => 2, 'payload' => ['correct_answer' => true]]);

    $result = trueFalseQuestion()->grade($question, null);

    expect($result->isCorrect)->toBeFalse();
});
