<?php

declare(strict_types=1);

namespace App\Services\Grading;

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Enums\FeedbackVisibility;
use App\Models\QuestionAnswer;
use App\Models\QuizAttempt;
use LogicException;

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
        $quiz = $attempt->quiz;

        if ($quiz === null) {
            throw new LogicException("QuizAttempt #{$attempt->id} has no Quiz.");
        }

        // See App\Services\Quizzes\QuizQuestionRandomizer for why this
        // fallback exists despite QuizSettingsCast::get() never actually
        // returning null.
        $settings = $quiz->settings ?? new QuizSettingsData;

        return match ($settings->feedbackVisibility) {
            FeedbackVisibility::Never => false,
            FeedbackVisibility::Immediate => true,
            FeedbackVisibility::AfterSubmission => $attempt->submitted_at !== null,
        };
    }
}
