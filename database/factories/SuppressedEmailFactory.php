<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SuppressionReason;
use App\Models\SuppressedEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SuppressedEmail>
 */
final class SuppressedEmailFactory extends Factory
{
    protected $model = SuppressedEmail::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'reason' => SuppressionReason::HardBounce,
            'provider_event_type' => 'permanent_fail',
            'occurred_at' => now(),
        ];
    }
}
