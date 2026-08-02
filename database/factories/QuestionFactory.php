<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Question;
use App\Models\Reseller;
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
            // Platform (shared) bank question by default -- pass
            // ->forReseller() for one owned by a specific reseller.
            'reseller_id' => null,
            'type' => 'multiple_choice',
            'prompt' => fake()->sentence().'?',
            'points' => 1,
            'settings' => null,
            'payload' => null,
        ];
    }

    public function forReseller(?int $resellerId = null): self
    {
        return $this->state([
            'reseller_id' => $resellerId ?? Reseller::factory(),
        ]);
    }
}
