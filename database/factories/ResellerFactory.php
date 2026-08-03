<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ResellerStatus;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Reseller>
 */
final class ResellerFactory extends Factory
{
    protected $model = Reseller::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'status' => ResellerStatus::Active,
            'settings' => [],
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ResellerStatus::Suspended,
        ]);
    }

    public function withAuthoringAddon(): static
    {
        return $this->state(fn (array $attributes): array => [
            'authoring_addon_expires_at' => now()->addYear(),
        ]);
    }
}
