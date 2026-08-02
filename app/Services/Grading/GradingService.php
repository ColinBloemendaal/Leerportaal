<?php

declare(strict_types=1);

namespace App\Services\Grading;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuizAttempt;
use App\Questions\QuestionTypeRegistry;
use Illuminate\Support\Carbon;

/**
 * Auto-grades what can be auto-graded, leaves the rest pending for a
 * human, and aggregates an attempt's answers into a final score/pass
 * verdict. "Regrade capability" isn't a separate method -- gradeAnswer()
 * is itself safely re-callable for auto-graded answers (e.g. after a
 * bank question's payload is corrected), and gradeManually() is both
 * "grade" and "regrade" for manually-graded ones.
 */
final readonly class GradingService
{
    public function __construct(private QuestionTypeRegistry $registry) {}

    public function gradeAnswer(QuestionAnswer $answer): QuestionAnswer
    {
        // Once classified as manual, only gradeManually() may touch this
        // answer again -- re-running the type's grade() would re-snapshot
        // points_possible from the question's current state and wipe out
        // any score a human has already recorded.
        if ($answer->requires_manual_grading) {
            return $answer;
        }

        $question = $answer->question;
        $type = $this->registry->resolve($question->type);
        $result = $type->grade($question, $answer->answer);

        $answer->points_awarded = $result->pointsAwarded;
        $answer->points_possible = $result->pointsPossible;
        $answer->is_correct = $result->isCorrect;
        $answer->requires_manual_grading = $result->requiresManualGrading;
        $answer->feedback = $result->feedback ?? $this->cannedFeedback($question, $result->isCorrect);
        $answer->graded_at = $result->requiresManualGrading ? null : Carbon::now();
        $answer->save();

        return $answer;
    }

    /**
     * Per-question canned feedback lives in `settings` (not `payload`,
     * which is type-specific), so it's a cross-cutting concept every
     * type can carry regardless of its own payload shape:
     * {"feedback": {"correct": "...", "incorrect": "..."}}. Only used
     * when the type's own GradeResult didn't already supply feedback.
     */
    private function cannedFeedback(Question $question, ?bool $isCorrect): ?string
    {
        if ($isCorrect === null) {
            return null;
        }

        $key = $isCorrect ? 'correct' : 'incorrect';

        return $question->settings['feedback'][$key] ?? null;
    }

    public function gradeManually(QuestionAnswer $answer, float $pointsAwarded, ?string $feedback = null): QuestionAnswer
    {
        $answer->points_awarded = $pointsAwarded;
        $answer->is_correct = $pointsAwarded >= $answer->points_possible;
        $answer->feedback = $feedback;
        $answer->graded_at = Carbon::now();
        $answer->save();

        return $answer;
    }

    public function gradeAttempt(QuizAttempt $attempt): QuizAttempt
    {
        $attempt->loadMissing('quiz');

        $answers = $attempt->answers()->with('question')->get()
            ->map(fn (QuestionAnswer $answer): QuestionAnswer => $this->gradeAnswer($answer));

        $hasPendingManualGrading = $answers->contains(
            fn (QuestionAnswer $answer): bool => $answer->requires_manual_grading && $answer->graded_at === null,
        );

        $attempt->score = (float) $answers->sum(fn (QuestionAnswer $answer): float => $answer->points_awarded ?? 0.0);
        $attempt->max_score = (float) $answers->sum('points_possible');

        $passThreshold = $attempt->quiz->settings->passThresholdPercent;

        $attempt->passed = match (true) {
            $hasPendingManualGrading, $passThreshold === null, $attempt->max_score <= 0.0 => null,
            default => ($attempt->score / $attempt->max_score * 100) >= $passThreshold,
        };

        $attempt->save();

        return $attempt;
    }
}
