<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {
 *   "template": string -- prose with blank markers, e.g. "The capital of France is {{1}}.",
 *   "blanks": [{"id": string, "acceptable_answers": [string, ...], "case_sensitive": bool}, ...]
 * }
 * `answer` (submitted): Record<blank id, string>.
 *
 * Partial credit: points * (correctly filled blanks / total blanks).
 * How `template` markers map to `blanks[].id` (and rendering them as
 * inputs) is the editor/player components' job, not this class's --
 * grading only cares about the blanks list and the submitted values.
 */
final class FillInBlankQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::FillInBlank;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::FillInBlank->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'template' => ['required', 'string'],
            'blanks' => ['required', 'array', 'min:1'],
            'blanks.*.id' => ['required', 'string'],
            'blanks.*.acceptable_answers' => ['required', 'array', 'min:1'],
            'blanks.*.acceptable_answers.*' => ['required', 'string'],
            'blanks.*.case_sensitive' => ['sometimes', 'boolean'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/FillInBlankQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/FillInBlankQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_array($answer)) {
            return GradeResult::incorrect($points);
        }

        /** @var list<array{id: string, acceptable_answers: list<string>, case_sensitive?: bool}> $blanks */
        $blanks = $question->payload['blanks'] ?? [];

        if ($blanks === []) {
            return GradeResult::incorrect($points);
        }

        $correctCount = 0;

        foreach ($blanks as $blank) {
            $submitted = $answer[$blank['id']] ?? null;

            if (is_string($submitted) && $this->matchesAny($submitted, $blank)) {
                $correctCount++;
            }
        }

        $total = count($blanks);

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

    /**
     * @param  array{id: string, acceptable_answers: list<string>, case_sensitive?: bool}  $blank
     */
    private function matchesAny(string $submitted, array $blank): bool
    {
        $caseSensitive = (bool) ($blank['case_sensitive'] ?? false);
        $submitted = trim($submitted);

        foreach ($blank['acceptable_answers'] as $candidate) {
            $matches = $caseSensitive
                ? $submitted === $candidate
                : mb_strtolower($submitted) === mb_strtolower($candidate);

            if ($matches) {
                return true;
            }
        }

        return false;
    }
}
