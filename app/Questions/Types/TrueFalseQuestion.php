<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {"correct_answer": bool}
 * `answer` (submitted): bool.
 */
final class TrueFalseQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::TrueFalse;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::TrueFalse->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'correct_answer' => ['required', 'boolean'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/TrueFalseQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/TrueFalseQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_bool($answer)) {
            return GradeResult::incorrect($points);
        }

        $correctAnswer = $question->payload['correct_answer'] ?? null;

        return $answer === $correctAnswer
            ? GradeResult::correct($points)
            : GradeResult::incorrect($points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }
}
