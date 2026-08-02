<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\HotspotImageQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function hotspotImageQuestion(): HotspotImageQuestion
{
    return new HotspotImageQuestion;
}

function hotspotImageQuestionModel(int $points = 6): Question
{
    return Question::factory()->create([
        'points' => $points,
        'payload' => [
            'image_url' => 'https://example.com/diagram.png',
            'regions' => [
                ['id' => 'a', 'x' => 10, 'y' => 10, 'width' => 20, 'height' => 20, 'label' => 'Heart'],
                ['id' => 'b', 'x' => 40, 'y' => 40, 'width' => 20, 'height' => 20, 'label' => 'Lungs'],
                ['id' => 'c', 'x' => 70, 'y' => 70, 'width' => 20, 'height' => 20, 'label' => 'Liver'],
            ],
            'correct_region_ids' => ['a', 'b'],
        ],
    ]);
}

it('reports its key and label', function (): void {
    expect(HotspotImageQuestion::key())->toBe(QuestionTypeEnum::HotspotImage)
        ->and(hotspotImageQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        [
            'image_url' => 'https://example.com/diagram.png',
            'regions' => [
                ['id' => 'a', 'x' => 0, 'y' => 0, 'width' => 10, 'height' => 10, 'label' => 'A'],
                ['id' => 'b', 'x' => 20, 'y' => 20, 'width' => 10, 'height' => 10, 'label' => 'B'],
            ],
            'correct_region_ids' => ['a'],
        ],
        hotspotImageQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('requires every region to have a text label for the keyboard alternative', function (): void {
    $validator = Validator::make(
        [
            'image_url' => 'https://example.com/diagram.png',
            'regions' => [
                ['id' => 'a', 'x' => 0, 'y' => 0, 'width' => 10, 'height' => 10],
                ['id' => 'b', 'x' => 20, 'y' => 20, 'width' => 10, 'height' => 10, 'label' => 'B'],
            ],
            'correct_region_ids' => ['a'],
        ],
        hotspotImageQuestion()->payloadRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('grades selecting exactly the correct regions as fully correct', function (): void {
    $result = hotspotImageQuestion()->grade(hotspotImageQuestionModel(), ['a', 'b']);

    expect($result->isCorrect)->toBeTrue()
        ->and($result->pointsAwarded)->toBe(6.0);
});

it('grades selecting one of two correct regions as partial credit', function (): void {
    $result = hotspotImageQuestion()->grade(hotspotImageQuestionModel(), ['a']);

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(3.0);
});

it('grades a malformed (non-array) answer as incorrect rather than erroring', function (): void {
    $result = hotspotImageQuestion()->grade(hotspotImageQuestionModel(), 'a');

    expect($result->isCorrect)->toBeFalse();
});

it('grades an empty answer as incorrect', function (): void {
    $result = hotspotImageQuestion()->grade(hotspotImageQuestionModel(), []);

    expect($result->isCorrect)->toBeFalse();
});
