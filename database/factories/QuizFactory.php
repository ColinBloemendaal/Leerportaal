<?php

declare(strict_types=1);

namespace Database\Factories;

use App\DataTransferObjects\Quizzes\QuizSettingsData;
use App\Enums\QuizType;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
final class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Attached to a lesson by default -- pass ->forModule() for an
            // end-of-module exam instead.
            'module_id' => null,
            'lesson_id' => Lesson::factory(),
            'type' => QuizType::Practice,
            'settings' => null,
        ];
    }

    public function forModule(?int $moduleId = null): self
    {
        return $this->state(fn (): array => [
            'module_id' => $moduleId ?? Module::factory(),
            'lesson_id' => null,
        ]);
    }

    public function exam(): self
    {
        return $this->state(['type' => QuizType::Exam]);
    }

    public function withSettings(QuizSettingsData $settings): self
    {
        return $this->state(['settings' => $settings]);
    }
}
