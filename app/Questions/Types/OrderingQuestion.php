<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {"items": [{"id": string, "text": string}, ...]} -- the
 * array order IS the correct order; items are shuffled for display and
 * the cursist reorders them back.
 * `answer` (submitted): list<string> of item ids in the submitted order.
 *
 * Partial credit: points * (items in their correct position / total
 * items) -- a positional comparison, not "is this a valid partial
 * ordering" (e.g. a single swap of two adjacent items only costs 2 of
 * N positions, not the whole question).
 */
final class OrderingQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::Ordering;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::Ordering->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'items' => ['required', 'array', 'min:2'],
            'items.*.id' => ['required', 'string'],
            'items.*.text' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/OrderingQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/OrderingQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_array($answer)) {
            return GradeResult::incorrect($points);
        }

        $items = $question->payload['items'] ?? [];
        $correctOrder = array_column($items, 'id');

        if ($correctOrder === []) {
            return GradeResult::incorrect($points);
        }

        $submitted = array_values($answer);
        $matches = 0;

        foreach ($correctOrder as $index => $id) {
            if (($submitted[$index] ?? null) === $id) {
                $matches++;
            }
        }

        $total = count($correctOrder);

        if ($matches === $total) {
            return GradeResult::correct($points);
        }

        if ($matches === 0) {
            return GradeResult::incorrect($points);
        }

        return GradeResult::partial($points * $matches / $total, $points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }
}
