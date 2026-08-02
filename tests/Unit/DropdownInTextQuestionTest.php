<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\DropdownInTextQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function dropdownInTextQuestion(): DropdownInTextQuestion
{
    return new DropdownInTextQuestion;
}

function dropdownInTextQuestionModel(int $points = 4): Question
{
    return Question::factory()->create([
        'points' => $points,
        'payload' => [
            'template' => 'The capital of France is {{1}}, in {{2}}.',
            'blanks' => [
                ['id' => '1', 'options' => ['Paris', 'Lyon', 'Berlin'], 'correct_option' => 'Paris'],
                ['id' => '2', 'options' => ['Europe', 'Asia'], 'correct_option' => 'Europe'],
            ],
        ],
    ]);
}

it('reports its key and label', function (): void {
    expect(DropdownInTextQuestion::key())->toBe(QuestionTypeEnum::DropdownInText)
        ->and(dropdownInTextQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        ['template' => '{{1}}', 'blanks' => [['id' => '1', 'options' => ['a', 'b'], 'correct_option' => 'a']]],
        dropdownInTextQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('grades selecting the correct option for every blank as fully correct', function (): void {
    $result = dropdownInTextQuestion()->grade(dropdownInTextQuestionModel(), ['1' => 'Paris', '2' => 'Europe']);

    expect($result->isCorrect())->toBeTrue()
        ->and($result->pointsAwarded)->toBe(4.0);
});

it('grades one correct blank as partial credit', function (): void {
    $result = dropdownInTextQuestion()->grade(dropdownInTextQuestionModel(), ['1' => 'Paris', '2' => 'Asia']);

    expect($result->isCorrect())->toBeFalse()
        ->and($result->pointsAwarded)->toBe(2.0);
});

it('is an exact match, unlike fill_in_blank -- casing must match precisely', function (): void {
    $result = dropdownInTextQuestion()->grade(dropdownInTextQuestionModel(), ['1' => 'paris', '2' => 'Europe']);

    expect($result->pointsAwarded)->toBe(2.0);
});

it('grades a malformed (non-array) answer as incorrect rather than erroring', function (): void {
    $result = dropdownInTextQuestion()->grade(dropdownInTextQuestionModel(), 'Paris');

    expect($result->isCorrect())->toBeFalse();
});

it('grades an empty answer as incorrect', function (): void {
    $result = dropdownInTextQuestion()->grade(dropdownInTextQuestionModel(), []);

    expect($result->isCorrect())->toBeFalse();
});
