<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {
 *   "template": string -- prose with blank markers, e.g. "The capital of {{1}} is {{2}}.",
 *   "blanks": [{"id": string, "options": [string, ...], "correct_option": string}, ...]
 * }
 * `answer` (submitted): Record<blank id, string> -- the option text chosen.
 *
 * Same template/blanks shape as fill_in_blank, but each blank is a fixed
 * dropdown rather than free text -- so matching is an exact string
 * comparison against `correct_option`, not fuzzy/case-insensitive
 * matching against a list of acceptable answers.
 */
final class DropdownInTextQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::DropdownInText;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::DropdownInText->label();
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
            'blanks.*.options' => ['required', 'array', 'min:2'],
            'blanks.*.options.*' => ['required', 'string'],
            'blanks.*.correct_option' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/DropdownInTextQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/DropdownInTextQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_array($answer)) {
            return GradeResult::incorrect($points);
        }

        /** @var list<array{id: string, correct_option: string}> $blanks */
        $blanks = $question->payload['blanks'] ?? [];

        if ($blanks === []) {
            return GradeResult::incorrect($points);
        }

        $correctCount = 0;

        foreach ($blanks as $blank) {
            if (($answer[$blank['id']] ?? null) === $blank['correct_option']) {
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
}
