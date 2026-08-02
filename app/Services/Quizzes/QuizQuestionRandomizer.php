<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Collection;

/**
 * "Question and answer randomization": this covers the question side --
 * which of a quiz's attached bank questions appear in a given attempt
 * (respecting settings->questionPoolSize), and what order they appear in
 * (respecting settings->shuffleQuestions). Answer-option order (e.g.
 * shuffling multiple_choice's options) is deliberately left to the
 * player components at render time -- every question type grades by
 * option id, never by position, so shuffling display order client-side
 * can never affect correctness. There's no consuming quiz-taking page
 * yet to wire that into (same recurring situation as the rest of this
 * phase), so building a generic server-side answer-shuffling mechanism
 * now would be speculative.
 */
final readonly class QuizQuestionRandomizer
{
    /**
     * @return Collection<int, Question>
     */
    public function questionsFor(Quiz $quiz): Collection
    {
        $questions = $quiz->questions()->get();
        // Larastan types Quiz::$settings as nullable, following the
        // underlying column's nullability, even though QuizSettingsCast::get()
        // always returns a real (if empty) QuizSettingsData -- this
        // fallback matches that actual runtime guarantee rather than
        // working around the type.
        $settings = $quiz->settings ?? new QuizSettingsData;

        if ($settings->questionPoolSize !== null && $settings->questionPoolSize < $questions->count()) {
            $questions = $questions->random($settings->questionPoolSize);
        }

        if ($settings->shuffleQuestions) {
            $questions = $questions->shuffle();
        }

        return $questions->values();
    }
}
