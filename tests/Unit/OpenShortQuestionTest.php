<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\OpenShortQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function openShortQuestion(): OpenShortQuestion
{
    return new OpenShortQuestion;
}

it('reports its key and label', function (): void {
    expect(OpenShortQuestion::key())->toBe(QuestionTypeEnum::OpenShort)
        ->and(openShortQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        ['match_mode' => 'exact', 'acceptable_answers' => ['Paris']],
        openShortQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects an unknown match_mode', function (): void {
    $validator = Validator::make(
        ['match_mode' => 'fuzzy', 'acceptable_answers' => ['Paris']],
        openShortQuestion()->payloadRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('grades an exact match case-insensitively by default', function (): void {
    $question = Question::factory()->create([
        'points' => 3,
        'payload' => ['match_mode' => 'exact', 'acceptable_answers' => ['Paris']],
    ]);

    expect(openShortQuestion()->grade($question, 'paris')->isCorrect)->toBeTrue();
});

it('rejects an exact match under case_sensitive when casing differs', function (): void {
    $question = Question::factory()->create([
        'points' => 3,
        'payload' => ['match_mode' => 'exact', 'case_sensitive' => true, 'acceptable_answers' => ['Paris']],
    ]);

    expect(openShortQuestion()->grade($question, 'paris')->isCorrect)->toBeFalse();
});

it('grades a contains match', function (): void {
    $question = Question::factory()->create([
        'points' => 3,
        'payload' => ['match_mode' => 'contains', 'acceptable_answers' => ['capital of france']],
    ]);

    $result = openShortQuestion()->grade($question, 'Paris is the capital of France, obviously');

    expect($result->isCorrect)->toBeTrue();
});

it('grades a regex match', function (): void {
    $question = Question::factory()->create([
        'points' => 3,
        'payload' => ['match_mode' => 'regex', 'acceptable_answers' => ['/^par[ei]s$/']],
    ]);

    expect(openShortQuestion()->grade($question, 'paris')->isCorrect)->toBeTrue()
        ->and(openShortQuestion()->grade($question, 'pares')->isCorrect)->toBeTrue()
        ->and(openShortQuestion()->grade($question, 'berlin')->isCorrect)->toBeFalse();
});

it('does not crash on a malformed regex pattern', function (): void {
    $question = Question::factory()->create([
        'points' => 3,
        'payload' => ['match_mode' => 'regex', 'acceptable_answers' => ['[invalid(']],
    ]);

    expect(openShortQuestion()->grade($question, 'anything')->isCorrect)->toBeFalse();
});

it('grades a blank or malformed answer as incorrect rather than erroring', function (): void {
    $question = Question::factory()->create([
        'points' => 3,
        'payload' => ['match_mode' => 'exact', 'acceptable_answers' => ['Paris']],
    ]);

    expect(openShortQuestion()->grade($question, '')->isCorrect)->toBeFalse()
        ->and(openShortQuestion()->grade($question, null)->isCorrect)->toBeFalse()
        ->and(openShortQuestion()->grade($question, ['Paris'])->isCorrect)->toBeFalse();
});
