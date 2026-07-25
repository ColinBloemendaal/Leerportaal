<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomDomainStatus;
use App\Models\CustomDomain;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomDomain>
 */
final class CustomDomainFactory extends Factory
{
    protected $model = CustomDomain::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            'domain' => fake()->unique()->domainName(),
            'status' => CustomDomainStatus::Pending,
            'verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CustomDomainStatus::Verified,
            'verified_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CustomDomainStatus::Failed,
        ]);
    }
}
