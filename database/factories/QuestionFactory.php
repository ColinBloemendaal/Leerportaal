<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
final class QuestionFactory extends Factory
{
    protected $model = Question::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            // A placeholder type string -- App\Enums\QuestionTypeEnum
            // doesn't exist until the next task.
            'type' => 'multiple_choice',
            'prompt' => fake()->sentence().'?',
            'points' => 1,
            'order' => 0,
            'settings' => null,
            'payload' => null,
        ];
    }
}
