<?php

declare(strict_types=1);

namespace App\Questions\Types;

use App\DataTransferObjects\Questions\GradeResult;
use App\Enums\QuestionTypeEnum;
use App\Models\Question;
use App\Questions\Contracts\QuestionType;

/**
 * `payload`: {"options": [{"id", "text"}, ...], "correct_option_ids": [string, ...]}
 * `answer` (submitted): the chosen options' ids, as a list<string>.
 *
 * Partial credit, Moodle-style: each of the N correct options is worth
 * points/N. Each correctly selected option adds that share; each
 * incorrectly selected option subtracts it. The result is clamped to
 * [0, points] so guessing everything never scores worse than guessing
 * nothing.
 */
final class MultipleResponseQuestion implements QuestionType
{
    public static function key(): QuestionTypeEnum
    {
        return QuestionTypeEnum::MultipleResponse;
    }

    public static function label(): string
    {
        return QuestionTypeEnum::MultipleResponse->label();
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
            'correct_option_ids' => ['required', 'array', 'min:1'],
            'correct_option_ids.*' => ['required', 'string'],
        ];
    }

    public function editorComponent(): string
    {
        return 'Questions/Editor/MultipleResponseQuestion';
    }

    public function playerComponent(): string
    {
        return 'Questions/Player/MultipleResponseQuestion';
    }

    public function grade(Question $question, mixed $answer): GradeResult
    {
        $points = (float) $question->points;

        if (! is_array($answer)) {
            return GradeResult::incorrect($points);
        }

        /** @var list<string> $correctOptionIds */
        $correctOptionIds = $question->payload['correct_option_ids'] ?? [];
        $selected = array_values(array_unique(array_filter($answer, 'is_string')));

        if ($correctOptionIds === []) {
            return GradeResult::incorrect($points);
        }

        if ($this->sameSet($selected, $correctOptionIds)) {
            return GradeResult::correct($points);
        }

        $pointsPerOption = $points / count($correctOptionIds);
        $score = 0.0;

        foreach ($selected as $optionId) {
            $score += in_array($optionId, $correctOptionIds, true) ? $pointsPerOption : -$pointsPerOption;
        }

        $score = max(0.0, min($points, $score));

        return $score > 0.0
            ? GradeResult::partial($score, $points)
            : GradeResult::incorrect($points);
    }

    public function isAutoGradable(): bool
    {
        return true;
    }

    /**
     * @param  list<string>  $a
     * @param  list<string>  $b
     */
    private function sameSet(array $a, array $b): bool
    {
        sort($a);
        sort($b);

        return $a === $b;
    }
}
