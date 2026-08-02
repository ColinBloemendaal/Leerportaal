<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\EssayQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function essayQuestion(): EssayQuestion
{
    return new EssayQuestion;
}

it('reports its key, label, and that it is not auto-gradable', function (): void {
    expect(EssayQuestion::key())->toBe(QuestionTypeEnum::Essay)
        ->and(essayQuestion()->isAutoGradable())->toBeFalse();
});

it('passes a payload with no rubric', function (): void {
    $validator = Validator::make([], essayQuestion()->payloadRules());

    expect($validator->passes())->toBeTrue();
});

it('passes a payload with a valid rubric', function (): void {
    $validator = Validator::make(
        ['rubric' => [['criterion' => 'Clarity', 'points' => 5], ['criterion' => 'Argument', 'points' => 5]]],
        essayQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects a rubric entry missing points', function (): void {
    $validator = Validator::make(
        ['rubric' => [['criterion' => 'Clarity']]],
        essayQuestion()->payloadRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('always reports pending manual grading, regardless of the answer', function (): void {
    $question = Question::factory()->create(['points' => 10]);

    expect(essayQuestion()->grade($question, 'A well-reasoned essay.')->requiresManualGrading)->toBeTrue()
        ->and(essayQuestion()->grade($question, '')->requiresManualGrading)->toBeTrue()
        ->and(essayQuestion()->grade($question, null)->requiresManualGrading)->toBeTrue();
});

it('has no correctness verdict until manually graded', function (): void {
    $question = Question::factory()->create(['points' => 10]);

    expect(essayQuestion()->grade($question, 'Anything')->isCorrect())->toBeNull();
});
