<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuizAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionAnswer>
 */
final class QuestionAnswerFactory extends Factory
{
    protected $model = QuestionAnswer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_attempt_id' => QuizAttempt::factory(),
            'question_id' => Question::factory(),
            'order' => 0,
            'answer' => null,
            'points_awarded' => null,
            'points_possible' => 1,
            'is_correct' => null,
            'requires_manual_grading' => false,
            'feedback' => null,
            'graded_at' => null,
        ];
    }

    public function graded(bool $isCorrect, float $pointsAwarded, float $pointsPossible): self
    {
        return $this->state(fn (): array => [
            'points_awarded' => $pointsAwarded,
            'points_possible' => $pointsPossible,
            'is_correct' => $isCorrect,
            'graded_at' => now(),
        ]);
    }
}
