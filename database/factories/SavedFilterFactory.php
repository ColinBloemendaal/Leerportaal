<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FilterableResource;
use App\Models\SavedFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedFilter>
 */
final class SavedFilterFactory extends Factory
{
    protected $model = SavedFilter::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'resource_type' => FilterableResource::Users,
            'name' => fake()->words(2, true),
            'filters' => ['search' => fake()->word()],
        ];
    }
}
