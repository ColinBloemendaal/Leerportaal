<?php

declare(strict_types=1);

namespace App\Actions\Questions;

use App\Models\Question;
use App\Models\Quiz;

final readonly class DetachQuestionFromQuiz
{
    public function __invoke(Quiz $quiz, Question $question): void
    {
        $quiz->questions()->detach($question->id);
    }
}
