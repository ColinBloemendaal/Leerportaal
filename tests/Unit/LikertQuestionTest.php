<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\LikertQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function likertQuestion(): LikertQuestion
{
    return new LikertQuestion;
}

it('reports its key, label, and that it is not auto-gradable', function (): void {
    expect(LikertQuestion::key())->toBe(QuestionTypeEnum::Likert)
        ->and(likertQuestion()->isAutoGradable())->toBeFalse();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        ['scale' => [
            ['value' => '1', 'label' => 'Strongly disagree'],
            ['value' => '5', 'label' => 'Strongly agree'],
        ]],
        likertQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('is always not applicable to grade, regardless of the answer', function (): void {
    $question = Question::factory()->create(['points' => 0]);

    $withAnswer = likertQuestion()->grade($question, '3');
    $withoutAnswer = likertQuestion()->grade($question, null);

    expect($withAnswer->isCorrect())->toBeNull()
        ->and($withAnswer->requiresManualGrading)->toBeFalse()
        ->and($withAnswer->pointsAwarded)->toBeNull()
        ->and($withoutAnswer->isCorrect())->toBeNull();
});
