<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Models\Quiz;

final readonly class AttachQuestionToQuiz
{
    public function __invoke(Quiz $quiz, Question $question, ?int $order = null): void
    {
        $order ??= $quiz->questions()->count();

        $quiz->questions()->syncWithoutDetaching([$question->id => ['order' => $order]]);
    }
}
