<?php

declare(strict_types=1);

namespace App\Actions\Quizzes;

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Exceptions\QuizAttemptNotAllowedException;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\Quizzes\QuizQuestionRandomizer;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Freezes the actual question set + order for this attempt as empty
 * question_answers placeholder rows (answer null, points_possible
 * snapshotted) -- QuizQuestionRandomizer::questionsFor() is only ever
 * called here, once. A page reload re-running it later could otherwise
 * show the cursist a *different* set of questions mid-attempt than the
 * one they started answering, which SubmitQuizAttempt has no way to
 * detect or correct after the fact.
 *
 * Resumes an existing not-yet-submitted attempt rather than starting a
 * new one -- attempt limits/cooldowns are about how many times a
 * cursist finishes a quiz, not how many times they reload the page
 * mid-attempt.
 */
final readonly class StartQuizAttempt
{
    public function __construct(
        private QuizQuestionRandomizer $randomizer,
        private ConnectionInterface $db,
    ) {}

    public function __invoke(Quiz $quiz, User $user): QuizAttempt
    {
        $inProgress = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->whereNull('submitted_at')
            ->first();

        if ($inProgress !== null) {
            return $inProgress;
        }

        $settings = $quiz->settings ?? new QuizSettingsData;

        $previousAttempts = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->orderByDesc('attempt_number')
            ->get();

        $this->assertAllowed($settings, $previousAttempts);

        return $this->db->transaction(function () use ($quiz, $user, $previousAttempts): QuizAttempt {
            $attempt = QuizAttempt::query()->create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'attempt_number' => $previousAttempts->count() + 1,
                'started_at' => now()->toImmutable(),
            ]);

            foreach ($this->randomizer->questionsFor($quiz)->values() as $index => $question) {
                $attempt->answers()->create([
                    'question_id' => $question->id,
                    'order' => $index,
                    'points_possible' => (float) $question->points,
                    'requires_manual_grading' => false,
                ]);
            }

            return $attempt;
        });
    }

    /**
     * @param  Collection<int, QuizAttempt>  $previousAttempts
     */
    private function assertAllowed(QuizSettingsData $settings, Collection $previousAttempts): void
    {
        if ($settings->attemptLimit !== null && $previousAttempts->count() >= $settings->attemptLimit) {
            throw QuizAttemptNotAllowedException::attemptLimitReached($settings->attemptLimit);
        }

        $lastSubmittedAt = $previousAttempts->first(fn (QuizAttempt $attempt): bool => $attempt->submitted_at !== null)?->submitted_at;

        if ($settings->cooldownMinutesBetweenAttempts !== null && $lastSubmittedAt !== null) {
            $availableAt = $lastSubmittedAt->addMinutes($settings->cooldownMinutesBetweenAttempts);

            if (now()->lessThan($availableAt)) {
                throw QuizAttemptNotAllowedException::cooldownActive($availableAt);
            }
        }
    }
}
