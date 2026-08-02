<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Quizzes;

use App\Enums\FeedbackVisibility;

/**
 * The typed shape of Quiz::$settings (a JSON column) -- see
 * App\Casts\QuizSettingsCast for the array <-> DTO conversion; this
 * class stays data-only per CLAUDE.md §9 (no methods beyond named
 * constructors).
 */
final readonly class QuizSettingsData
{
    public function __construct(
        public ?int $timeLimitMinutes = null,
        public ?int $attemptLimit = null,
        public ?int $passThresholdPercent = null,
        public ?int $cooldownMinutesBetweenAttempts = null,
        public ?int $questionPoolSize = null,
        public bool $shuffleQuestions = false,
        public FeedbackVisibility $feedbackVisibility = FeedbackVisibility::AfterSubmission,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            timeLimitMinutes: $data['time_limit_minutes'] ?? null,
            attemptLimit: $data['attempt_limit'] ?? null,
            passThresholdPercent: $data['pass_threshold_percent'] ?? null,
            cooldownMinutesBetweenAttempts: $data['cooldown_minutes_between_attempts'] ?? null,
            questionPoolSize: $data['question_pool_size'] ?? null,
            shuffleQuestions: $data['shuffle_questions'] ?? false,
            feedbackVisibility: isset($data['feedback_visibility'])
                ? FeedbackVisibility::from($data['feedback_visibility'])
                : FeedbackVisibility::AfterSubmission,
        );
    }
}
