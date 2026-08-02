<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Courses;

use App\Enums\CourseCompletionRuleType;

/**
 * The typed shape of Course::$completion_rule -- see
 * App\Casts\CourseCompletionRuleCast for the array <-> DTO conversion,
 * same split as QuizSettingsData/QuizSettingsCast.
 */
final readonly class CourseCompletionRuleData
{
    public function __construct(
        public CourseCompletionRuleType $type = CourseCompletionRuleType::AllLessons,
        public ?int $examQuizId = null,
        public ?int $minimumScorePercent = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: isset($data['type']) ? CourseCompletionRuleType::from($data['type']) : CourseCompletionRuleType::AllLessons,
            examQuizId: $data['exam_quiz_id'] ?? null,
            minimumScorePercent: $data['minimum_score_percent'] ?? null,
        );
    }
}
