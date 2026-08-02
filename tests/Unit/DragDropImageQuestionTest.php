<?php

declare(strict_types=1);

use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Types\DragDropImageQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function dragDropImageQuestion(): DragDropImageQuestion
{
    return new DragDropImageQuestion;
}

function dragDropImageQuestionModel(int $points = 4): Question
{
    return Question::factory()->create([
        'points' => $points,
        'payload' => [
            'image_url' => 'https://example.com/map.png',
            'drop_zones' => [
                ['id' => 'zone-1', 'x' => 10, 'y' => 10, 'width' => 20, 'height' => 20, 'label' => 'Zone 1'],
                ['id' => 'zone-2', 'x' => 40, 'y' => 40, 'width' => 20, 'height' => 20, 'label' => 'Zone 2'],
            ],
            'draggable_items' => [
                ['id' => 'item-a', 'text' => 'France'],
                ['id' => 'item-b', 'text' => 'Germany'],
            ],
            'correct_placements' => ['zone-1' => 'item-a', 'zone-2' => 'item-b'],
        ],
    ]);
}

it('reports its key and label', function (): void {
    expect(DragDropImageQuestion::key())->toBe(QuestionTypeEnum::DragDropImage)
        ->and(dragDropImageQuestion()->isAutoGradable())->toBeTrue();
});

it('passes a valid payload', function (): void {
    $validator = Validator::make(
        [
            'image_url' => 'https://example.com/map.png',
            'drop_zones' => [
                ['id' => 'z1', 'x' => 0, 'y' => 0, 'width' => 10, 'height' => 10, 'label' => 'Zone 1'],
                ['id' => 'z2', 'x' => 20, 'y' => 20, 'width' => 10, 'height' => 10, 'label' => 'Zone 2'],
            ],
            'draggable_items' => [['id' => 'a', 'text' => 'A'], ['id' => 'b', 'text' => 'B']],
            'correct_placements' => ['z1' => 'a', 'z2' => 'b'],
        ],
        dragDropImageQuestion()->payloadRules(),
    );

    expect($validator->passes())->toBeTrue();
});

it('requires every drop zone to have a text label for the keyboard alternative', function (): void {
    $validator = Validator::make(
        [
            'image_url' => 'https://example.com/map.png',
            'drop_zones' => [
                ['id' => 'z1', 'x' => 0, 'y' => 0, 'width' => 10, 'height' => 10],
                ['id' => 'z2', 'x' => 20, 'y' => 20, 'width' => 10, 'height' => 10, 'label' => 'Zone 2'],
            ],
            'draggable_items' => [['id' => 'a', 'text' => 'A'], ['id' => 'b', 'text' => 'B']],
            'correct_placements' => ['z1' => 'a', 'z2' => 'b'],
        ],
        dragDropImageQuestion()->payloadRules(),
    );

    expect($validator->fails())->toBeTrue();
});

it('grades placing every item in its correct zone as fully correct', function (): void {
    $result = dragDropImageQuestion()->grade(dragDropImageQuestionModel(), [
        'zone-1' => 'item-a', 'zone-2' => 'item-b',
    ]);

    expect($result->isCorrect)->toBeTrue()
        ->and($result->pointsAwarded)->toBe(4.0);
});

it('grades one correct placement as partial credit', function (): void {
    $result = dragDropImageQuestion()->grade(dragDropImageQuestionModel(), [
        'zone-1' => 'item-a', 'zone-2' => 'item-a',
    ]);

    expect($result->isCorrect)->toBeFalse()
        ->and($result->pointsAwarded)->toBe(2.0);
});

it('grades a malformed (non-array) answer as incorrect rather than erroring', function (): void {
    $result = dragDropImageQuestion()->grade(dragDropImageQuestionModel(), 'item-a');

    expect($result->isCorrect)->toBeFalse();
});

it('grades an empty answer as incorrect', function (): void {
    $result = dragDropImageQuestion()->grade(dragDropImageQuestionModel(), []);

    expect($result->isCorrect)->toBeFalse();
});
