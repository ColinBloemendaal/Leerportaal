<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\OrderingQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function orderingQuestion(): OrderingQuestion
{
    return new OrderingQuestion;
}

function orderingQuestionModel(int $points = 4): Question
{
    return Question::factory()->create([
        'points' => $points,
        'payload' => [
            'items' => [
                ['id' => 'a', 'text' => 'First'],
                ['id' => 'b', 'text' => 'Second'],
                ['id' => 'c', 'text' => 'Third'],
                ['id' => 'd', 'text' => 'Fourth'],
            ],
        ],
    ]);
}

it('reports its key and label', function (): void {
    expect(OrderingQuestion::key())->toBe(QuestionTypeEnum::Ordering)
        ->and(orderingQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        ['items' => [['id' => 'a', 'text' => 'First'], ['id' => 'b', 'text' => 'Second']]],
        orderingQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('grades the exact correct order as fully correct', function (): void {
    $result = orderingQuestion()->grade(orderingQuestionModel(), ['a', 'b', 'c', 'd']);

    expect($result->isCorrect())->toBeTrue()
        ->and($result->pointsAwarded)->toBe(4.0);
});

it('grades a partially correct order proportionally', function (): void {
    // a and d are in their correct positions (2 of 4); b/c are swapped.
    $result = orderingQuestion()->grade(orderingQuestionModel(), ['a', 'c', 'b', 'd']);

    expect($result->isCorrect())->toBeFalse()
        ->and($result->pointsAwarded)->toBe(2.0);
});

it('grades a fully reversed order as incorrect', function (): void {
    $result = orderingQuestion()->grade(orderingQuestionModel(), ['d', 'c', 'b', 'a']);

    expect($result->isCorrect())->toBeFalse()
        ->and($result->pointsAwarded)->toBe(0.0);
});

it('grades a malformed (non-array) answer as incorrect rather than erroring', function (): void {
    $result = orderingQuestion()->grade(orderingQuestionModel(), 'a,b,c,d');

    expect($result->isCorrect())->toBeFalse();
});

it('grades an empty answer as incorrect', function (): void {
    $result = orderingQuestion()->grade(orderingQuestionModel(), []);

    expect($result->isCorrect())->toBeFalse();
});
