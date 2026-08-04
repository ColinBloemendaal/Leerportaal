<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Models\QuestionAnswer;
use App\Models\QuizAttempt;
use App\Questions\QuestionTypeRegistry;
use App\Services\Grading\AnswerFeedbackPresenter;
use LogicException;

/**
 * Shapes one QuizAttempt (with its frozen question_answers) into the
 * plain array the quiz-taking Inertia page renders. Not a
 * Http\Resources class: this needs QuestionTypeRegistry (to resolve
 * each question's playerComponent()) and AnswerFeedbackPresenter (to
 * decide per-answer feedback visibility), neither of which a
 * constructor-less JsonResource can take.
 */
final readonly class QuizAttemptPresenter
{
    public function __construct(
        private QuestionTypeRegistry $registry,
        private AnswerFeedbackPresenter $feedback,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(QuizAttempt $attempt): array
    {
        $attempt->loadMissing(['quiz', 'answers.question']);
        $settings = $attempt->quiz->settings ?? new QuizSettingsData;

        return [
            'id' => $attempt->id,
            'attemptNumber' => $attempt->attempt_number,
            'startedAt' => $attempt->started_at->toIso8601String(),
            'submittedAt' => $attempt->submitted_at?->toIso8601String(),
            'score' => $attempt->score,
            'maxScore' => $attempt->max_score,
            'passed' => $attempt->passed,
            'timeLimitMinutes' => $settings->timeLimitMinutes,
            'questions' => $attempt->answers
                ->sortBy('order')
                ->values()
                ->map(fn (QuestionAnswer $answer): array => $this->presentAnswer($attempt, $answer))
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentAnswer(QuizAttempt $attempt, QuestionAnswer $answer): array
    {
        $question = $answer->question;

        if ($question === null) {
            throw new LogicException("QuestionAnswer #{$answer->id} has no Question.");
        }

        return [
            'answerId' => $answer->id,
            'questionId' => $answer->question_id,
            'prompt' => $question->prompt,
            'payload' => $question->payload,
            'playerComponent' => $this->registry->resolve($question->type)->playerComponent(),
            'submittedAnswer' => $answer->answer,
            'pointsAwarded' => $answer->points_awarded,
            'pointsPossible' => $answer->points_possible,
            'isCorrect' => $answer->is_correct,
            'feedback' => $this->feedback->feedbackFor($attempt, $answer),
        ];
    }
}
