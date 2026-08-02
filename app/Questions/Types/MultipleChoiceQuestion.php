<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {"options": [{"id": string, "text": string}, ...], "correct_option_id": string}
 * `answer` (submitted): the chosen option's `id`, a string.
 */
final class MultipleChoiceQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::MultipleChoice;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::MultipleChoice->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'options' => ['required', 'array', 'min:2'],
            'options.*.id' => ['required', 'string'],
            'options.*.text' => ['required', 'string'],
            'correct_option_id' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/MultipleChoiceQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/MultipleChoiceQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_string($answer)) {
            return GradeResult::incorrect($points);
        }

        $correctOptionId = $question->payload['correct_option_id'] ?? null;

        return $answer === $correctOptionId
            ? GradeResult::correct($points)
            : GradeResult::incorrect($points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }
}
