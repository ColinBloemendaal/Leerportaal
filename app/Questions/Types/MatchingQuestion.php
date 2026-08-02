<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {"pairs": [{"id": string, "left": string, "right": string}, ...]}
 * `answer` (submitted): Record<pair id, chosen right text> -- the cursist
 * matches each shuffled `right` value back to a `left` prompt.
 *
 * Assumes `right` values are unique within a question (the editor's job
 * to enforce, not this schema-level type) -- there's no separate id for
 * right options, to keep the payload to one flat list rather than two
 * cross-referenced ones.
 *
 * Partial credit: points * (correctly matched pairs / total pairs).
 */
final class MatchingQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::Matching;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::Matching->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'pairs' => ['required', 'array', 'min:2'],
            'pairs.*.id' => ['required', 'string'],
            'pairs.*.left' => ['required', 'string'],
            'pairs.*.right' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/MatchingQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/MatchingQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_array($answer)) {
            return GradeResult::incorrect($points);
        }

        /** @var list<array{id: string, left: string, right: string}> $pairs */
        $pairs = $question->payload['pairs'] ?? [];

        if ($pairs === []) {
            return GradeResult::incorrect($points);
        }

        $correctCount = 0;

        foreach ($pairs as $pair) {
            if (($answer[$pair['id']] ?? null) === $pair['right']) {
                $correctCount++;
            }
        }

        if ($correctCount === count($pairs)) {
            return GradeResult::correct($points);
        }

        if ($correctCount === 0) {
            return GradeResult::incorrect($points);
        }

        return GradeResult::partial($points * $correctCount / count($pairs), $points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }
}
