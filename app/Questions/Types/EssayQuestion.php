<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {
 *   "rubric": [{"criterion": string, "points": number}, ...] (optional),
 *   "min_words": int|null,
 *   "max_words": int|null
 * }
 * `answer` (submitted): string (the essay text).
 *
 * Never auto-gradable -- grade() always reports pending regardless of
 * what was submitted (even blank/malformed), since only a human grader
 * can award a score here. Whatever happens to an ungraded/empty
 * submission when a quiz attempt is finalized is the grading service's
 * business rule (a later task), not this type's.
 */
final class EssayQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::Essay;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::Essay->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'rubric' => ['sometimes', 'array'],
            'rubric.*.criterion' => ['required_with:rubric', 'string'],
            'rubric.*.points' => ['required_with:rubric', 'numeric', 'min:0'],
            'min_words' => ['nullable', 'integer', 'min:0'],
            'max_words' => ['nullable', 'integer', 'gte:min_words'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/EssayQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/EssayQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        return GradeResult::pendingManualGrading((float) $question->points);
    }

    public function isAutoGradable(): bool
    {
        return false;
    }
}
