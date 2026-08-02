<?php

declare(strict_types=1);

namespace App\Casts;

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<QuizSettingsData, QuizSettingsData|array<string, mixed>|null>
 */
final class QuizSettingsCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): QuizSettingsData
    {
        if ($value === null) {
            return new QuizSettingsData;
        }

        /** @var array<string, mixed> $decoded */
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return QuizSettingsData::fromArray($decoded);
    }

    /**
     * Returns a JSON-encoded string, not a raw array -- unlike Laravel's
     * multi-column custom-cast examples (e.g. an Address split across
     * two columns), `settings` is one `json` column, so the value bound
     * to the query must already be an encoded string.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $settings = $value instanceof QuizSettingsData ? $value : QuizSettingsData::fromArray((array) $value);

        return json_encode([
            'time_limit_minutes' => $settings->timeLimitMinutes,
            'attempt_limit' => $settings->attemptLimit,
            'pass_threshold_percent' => $settings->passThresholdPercent,
            'cooldown_minutes_between_attempts' => $settings->cooldownMinutesBetweenAttempts,
            'question_pool_size' => $settings->questionPoolSize,
            'shuffle_questions' => $settings->shuffleQuestions,
            'feedback_visibility' => $settings->feedbackVisibility->value,
        ], JSON_THROW_ON_ERROR);
    }
}
