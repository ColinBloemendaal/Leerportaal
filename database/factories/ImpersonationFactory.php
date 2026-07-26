<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Impersonation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Impersonation>
 */
final class ImpersonationFactory extends Factory
{
    protected $model = Impersonation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'impersonator_user_id' => User::factory(),
            'impersonated_user_id' => User::factory(),
            'reason' => fake()->sentence(),
            'started_at' => now(),
            'ended_at' => null,
        ];
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'ended_at' => now(),
        ]);
    }
}
