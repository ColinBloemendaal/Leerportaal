<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {
 *   "image_url": string,
 *   "drop_zones": [{"id", "x", "y", "width", "height", "label"}, ...] -- percentages, labeled,
 *   "draggable_items": [{"id", "text"}, ...],
 *   "correct_placements": Record<drop zone id, correct item id>
 * }
 * `answer` (submitted): Record<drop zone id, chosen item id>.
 *
 * Same "labeled zones enable a real keyboard path" reasoning as
 * hotspot_image: the player's actual answer-submission mechanism is a
 * `<select>` per drop zone listing the draggable items by their text
 * (same approach as the `matching` type), not the drag gesture itself
 * -- the drag interaction on the image is a visual convenience layered
 * on top for mouse/touch users.
 *
 * Partial credit: points * (correctly filled zones / total zones).
 */
final class DragDropImageQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::DragDropImage;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::DragDropImage->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'image_url' => ['required', 'string', 'url'],
            'drop_zones' => ['required', 'array', 'min:2'],
            'drop_zones.*.id' => ['required', 'string'],
            'drop_zones.*.x' => ['required', 'numeric', 'min:0', 'max:100'],
            'drop_zones.*.y' => ['required', 'numeric', 'min:0', 'max:100'],
            'drop_zones.*.width' => ['required', 'numeric', 'min:0', 'max:100'],
            'drop_zones.*.height' => ['required', 'numeric', 'min:0', 'max:100'],
            'drop_zones.*.label' => ['required', 'string'],
            'draggable_items' => ['required', 'array', 'min:2'],
            'draggable_items.*.id' => ['required', 'string'],
            'draggable_items.*.text' => ['required', 'string'],
            'correct_placements' => ['required', 'array', 'min:1'],
            'correct_placements.*' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/DragDropImageQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/DragDropImageQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_array($answer)) {
            return GradeResult::incorrect($points);
        }

        /** @var array<string, string> $correctPlacements */
        $correctPlacements = $question->payload['correct_placements'] ?? [];

        if ($correctPlacements === []) {
            return GradeResult::incorrect($points);
        }

        $correctCount = 0;

        foreach ($correctPlacements as $zoneId => $correctItemId) {
            if (($answer[$zoneId] ?? null) === $correctItemId) {
                $correctCount++;
            }
        }

        $total = count($correctPlacements);

        if ($correctCount === $total) {
            return GradeResult::correct($points);
        }

        if ($correctCount === 0) {
            return GradeResult::incorrect($points);
        }

        return GradeResult::partial($points * $correctCount / $total, $points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }
}
