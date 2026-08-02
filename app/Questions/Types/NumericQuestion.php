<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {"correct_answer": number, "tolerance": number} -- tolerance
 * is an absolute margin (`correct_answer ± tolerance`), defaults to 0
 * (exact match) when omitted.
 * `answer` (submitted): number, or a numeric string (form inputs commonly
 * submit numbers as strings).
 */
final class NumericQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::Numeric;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::Numeric->label();
    }

    /**
     * @return array<string, mixed>
     */
    public function payloadRules(): array
    {
        return [
            'correct_answer' => ['required', 'numeric'],
            'tolerance' => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/NumericQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/NumericQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_int($answer) && ! is_float($answer) && ! (is_string($answer) && is_numeric($answer))) {
            return GradeResult::incorrect($points);
        }

        $payload = $question->payload ?? [];

        if (! isset($payload['correct_answer']) || ! is_numeric($payload['correct_answer'])) {
            return GradeResult::incorrect($points);
        }

        $submitted = (float) $answer;
        $correctAnswer = (float) $payload['correct_answer'];
        $tolerance = isset($payload['tolerance']) && is_numeric($payload['tolerance'])
            ? (float) $payload['tolerance']
            : 0.0;

        return abs($submitted - $correctAnswer) <= $tolerance
            ? GradeResult::correct($points)
            : GradeResult::incorrect($points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }
}
