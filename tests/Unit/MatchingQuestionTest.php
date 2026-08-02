<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\MatchingQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function matchingQuestion(): MatchingQuestion
{
    return new MatchingQuestion;
}

function matchingQuestionModel(int $points = 6): Question
{
    return Question::factory()->create([
        'points' => $points,
        'payload' => [
            'pairs' => [
                ['id' => '1', 'left' => 'Paris', 'right' => 'France'],
                ['id' => '2', 'left' => 'Berlin', 'right' => 'Germany'],
                ['id' => '3', 'left' => 'Madrid', 'right' => 'Spain'],
            ],
        ],
    ]);
}

it('reports its key and label', function (): void {
    expect(MatchingQuestion::key())->toBe(QuestionTypeEnum::Matching)
        ->and(matchingQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        ['pairs' => [
            ['id' => '1', 'left' => 'Paris', 'right' => 'France'],
            ['id' => '2', 'left' => 'Berlin', 'right' => 'Germany'],
        ]],
        matchingQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('grades matching every pair correctly as fully correct', function (): void {
    $result = matchingQuestion()->grade(matchingQuestionModel(), [
        '1' => 'France', '2' => 'Germany', '3' => 'Spain',
    ]);

    expect($result->isCorrect)->toBeTrue()
        ->and($result->pointsAwarded)->toBe(6.0);
});

it('grades matching some pairs as partial credit', function (): void {
    $result = matchingQuestion()->grade(matchingQuestionModel(), [
        '1' => 'France', '2' => 'Spain', '3' => 'Germany',
    ]);

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(2.0);
});

it('grades matching no pairs as incorrect', function (): void {
    $result = matchingQuestion()->grade(matchingQuestionModel(), [
        '1' => 'Germany', '2' => 'Spain', '3' => 'France',
    ]);

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(0.0);
});

it('grades a malformed (non-array) answer as incorrect rather than erroring', function (): void {
    $result = matchingQuestion()->grade(matchingQuestionModel(), 'France');

    expect($result->isCorrect)->toBeFalse();
});

it('grades an empty answer as incorrect', function (): void {
    $result = matchingQuestion()->grade(matchingQuestionModel(), []);

    expect($result->isCorrect)->toBeFalse();
});
