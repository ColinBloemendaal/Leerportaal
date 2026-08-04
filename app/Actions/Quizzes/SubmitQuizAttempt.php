<?php

declare(strict_types=1);

namespace App\Actions\Quizzes;

use App\Exceptions\QuizAttemptNotAllowedException;
use App\Models\QuizAttempt;
use App\Services\Grading\GradingService;
use Illuminate\Database\ConnectionInterface;

/**
 * Writes the cursist's submitted answers into the question_answers rows
 * StartQuizAttempt already froze (order + points_possible), then grades
 * the whole attempt. Never creates or reorders question_answers rows --
 * that would defeat the point of freezing the set at start time.
 */
final readonly class SubmitQuizAttempt
{
    public function __construct(
        private GradingService $grading,
        private ConnectionInterface $db,
    ) {}

    /**
     * @param  array<int, mixed>  $answersByQuestionId
     */
    public function __invoke(QuizAttempt $attempt, array $answersByQuestionId): QuizAttempt
    {
        if ($attempt->submitted_at !== null) {
            throw QuizAttemptNotAllowedException::alreadySubmitted();
        }

        return $this->db->transaction(function () use ($attempt, $answersByQuestionId): QuizAttempt {
            $attempt->loadMissing('answers');

            foreach ($attempt->answers as $answer) {
                if (array_key_exists($answer->question_id, $answersByQuestionId)) {
                    $answer->answer = $answersByQuestionId[$answer->question_id];
                    $answer->save();
                }
            }

            $attempt->submitted_at = now()->toImmutable();
            $attempt->save();

            $graded = $this->grading->gradeAttempt($attempt);
            // gradeAttempt() grades from its own fresh query, which never
            // writes back to $attempt's already-cached `answers` relation
            // -- reload so the caller doesn't see the pre-grading values.
            $graded->load('answers');

            return $graded;
        });
    }
}
