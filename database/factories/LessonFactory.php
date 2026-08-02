<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
final class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'title' => fake()->words(3, true),
            'order' => 0,
        ];
    }
}
