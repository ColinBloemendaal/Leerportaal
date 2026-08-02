<?php

declare(strict_types=1);

namespace App\Services\Grading;

use App\Enums\FeedbackVisibility;
use App\Models\QuestionAnswer;
use App\Models\QuizAttempt;

/**
 * "Shown per configurable rules": whether a graded answer's feedback is
 * actually exposed to the cursist depends on the quiz's
 * FeedbackVisibility setting, independent of whether the answer itself
 * has been graded.
 */
final readonly class AnswerFeedbackPresenter
{
    public function feedbackFor(QuizAttempt $attempt, QuestionAnswer $answer): ?string
    {
        return $this->isVisible($attempt) ? $answer->feedback : null;
    }

    private function isVisible(QuizAttempt $attempt): bool
    {
        return match ($attempt->quiz->settings->feedbackVisibility) {
            FeedbackVisibility::Never => false,
            FeedbackVisibility::Immediate => true,
            FeedbackVisibility::AfterSubmission => $attempt->submitted_at !== null,
        };
    }
}
