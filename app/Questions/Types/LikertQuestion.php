<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {"scale": [{"value": string, "label": string}, ...]} -- e.g.
 * a 1-5 agreement scale, each point given an explicit label.
 * `answer` (submitted): the chosen scale point's `value`.
 *
 * Non-scored per CLAUDE.md §5: grade() always reports notApplicable(),
 * never correct/incorrect/pending -- a survey scale has no concept of a
 * right answer to withhold or defer, unlike essay's pendingManualGrading.
 */
final class LikertQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::Likert;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::Likert->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'scale' => ['required', 'array', 'min:2'],
            'scale.*.value' => ['required', 'string'],
            'scale.*.label' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/LikertQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/LikertQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        return GradeResult::notApplicable();
    }

    public function isAutoGradable(): bool
    {
        return false;
    }
}
