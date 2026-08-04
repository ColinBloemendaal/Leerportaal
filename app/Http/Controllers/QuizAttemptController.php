<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Quizzes\StartQuizAttempt;
use App\Actions\Quizzes\SubmitQuizAttempt;
use App\Contracts\Repositories\QuizAttemptRepository;
use App\Contracts\Repositories\QuizRepository;
use App\Exceptions\QuizAttemptNotAllowedException;
use App\Http\Requests\Quizzes\SubmitQuizAttemptRequest;
use App\Services\Quizzes\QuizAttemptPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * TODO.md Phase 8 gap: closes the "no Action anywhere submits a quiz
 * attempt" gap. Reachable by a direct route only -- there is no
 * cursist-facing course/lesson/block viewing page anywhere in this
 * codebase yet (the Dashboard's assignment cards aren't links to
 * anything), and building that whole experience is out of this gap's
 * scope.
 */
final class QuizAttemptController extends Controller
{
    public function show(int $quiz, Request $request, QuizRepository $quizzes, QuizAttemptRepository $attempts, StartQuizAttempt $start, QuizAttemptPresenter $presenter): Response
    {
        $foundQuiz = $quizzes->findById($quiz);
        abort_if($foundQuiz === null, 404);

        $this->authorize('start', $foundQuiz);

        $user = $request->user();
        abort_if($user === null, 401);

        try {
            $attempt = $start($foundQuiz, $user);
        } catch (QuizAttemptNotAllowedException $e) {
            $lastAttempt = $attempts->latestSubmittedForQuizAndUser($foundQuiz->id, $user->id);

            return Inertia::render('Quizzes/Attempt', [
                'blockedReason' => $e->getMessage(),
                'attempt' => null,
                'lastResult' => $lastAttempt === null ? null : $presenter->present($lastAttempt),
            ]);
        }

        return Inertia::render('Quizzes/Attempt', [
            'blockedReason' => null,
            'attempt' => $presenter->present($attempt),
            'lastResult' => null,
        ]);
    }

    public function submit(SubmitQuizAttemptRequest $request, int $attempt, QuizAttemptRepository $attempts, SubmitQuizAttempt $submit): RedirectResponse
    {
        $foundAttempt = $attempts->findById($attempt);
        abort_if($foundAttempt === null, 404);

        $submit($foundAttempt, $request->toDto()->answersByQuestionId);

        return to_route('quizzes.attempt.show', $foundAttempt->quiz_id)->with('success', __('Quiz submitted.'));
    }
}
