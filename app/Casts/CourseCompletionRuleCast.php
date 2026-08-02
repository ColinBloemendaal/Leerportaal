<?php

declare(strict_types=1);

namespace App\Casts;

use App\DataTransferObjects\Courses\CourseCompletionRuleData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<CourseCompletionRuleData, CourseCompletionRuleData|array<string, mixed>|null>
 */
final class CourseCompletionRuleCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): CourseCompletionRuleData
    {
        if ($value === null) {
            return new CourseCompletionRuleData;
        }

        /** @var array<string, mixed> $decoded */
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return CourseCompletionRuleData::fromArray($decoded);
    }

    /**
     * Returns a JSON-encoded string, not a raw array -- see
     * QuizSettingsCast::set() for why.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $rule = $value instanceof CourseCompletionRuleData ? $value : CourseCompletionRuleData::fromArray((array) $value);

        return json_encode([
            'type' => $rule->type->value,
            'exam_quiz_id' => $rule->examQuizId,
            'minimum_score_percent' => $rule->minimumScorePercent,
        ], JSON_THROW_ON_ERROR);
    }
}
