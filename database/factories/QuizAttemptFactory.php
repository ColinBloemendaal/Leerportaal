<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
final class QuizAttemptFactory extends Factory
{
    protected $model = QuizAttempt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'user_id' => User::factory(),
            'attempt_number' => 1,
            'started_at' => now(),
            'submitted_at' => null,
            'score' => null,
            'max_score' => null,
            'passed' => null,
        ];
    }

    public function submitted(): self
    {
        return $this->state(fn (): array => [
            'submitted_at' => now(),
        ]);
    }
}
