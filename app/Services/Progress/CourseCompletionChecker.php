<?php

declare(strict_types=1);

namespace App\Services\Progress;

use App\DataTransferObjects\Courses\CourseCompletionRuleData;
use App\Enums\CourseCompletionRuleType;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\QuizAttempt;

/**
 * Whether a cursist has completed a course, per that course's own
 * configurable rule (all lessons / pass a specific exam / reach a
 * minimum score on that exam) -- CLAUDE.md's own three named options,
 * not an open-ended rules engine.
 */
final readonly class CourseCompletionChecker
{
    public function __construct(private ProgressCalculationService $progress) {}

    public function isComplete(CourseAssignment $assignment, Course $course): bool
    {
        $rule = $course->completion_rule;

        return match ($rule->type) {
            CourseCompletionRuleType::AllLessons => $this->progress->courseCompletionPercent($assignment, $course) >= 100.0,
            CourseCompletionRuleType::PassExam => $this->bestAttempt($assignment, $rule->examQuizId)?->passed === true,
            CourseCompletionRuleType::MinimumScore => $this->meetsMinimumScore($assignment, $rule),
        };
    }

    private function meetsMinimumScore(CourseAssignment $assignment, CourseCompletionRuleData $rule): bool
    {
        $attempt = $this->bestAttempt($assignment, $rule->examQuizId);

        if ($attempt === null || $rule->minimumScorePercent === null) {
            return false;
        }

        if ($attempt->max_score === null || $attempt->max_score <= 0.0 || $attempt->score === null) {
            return false;
        }

        return ($attempt->score / $attempt->max_score * 100) >= $rule->minimumScorePercent;
    }

    /**
     * The highest-scoring submitted attempt at the rule's designated
     * exam quiz, for this assignment's cursist.
     */
    private function bestAttempt(CourseAssignment $assignment, ?int $quizId): ?QuizAttempt
    {
        if ($quizId === null) {
            return null;
        }

        return QuizAttempt::query()
            ->where('user_id', $assignment->user_id)
            ->where('quiz_id', $quizId)
            ->whereNotNull('submitted_at')
            ->orderByDesc('score')
            ->first();
    }
}
