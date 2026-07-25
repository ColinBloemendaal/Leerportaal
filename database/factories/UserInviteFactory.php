<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Reseller;
use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserInvite>
 */
final class UserInviteFactory extends Factory
{
    protected $model = UserInvite::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            'resellerklant_id' => null,
            'invited_by_user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => Role::Cursist,
            'accepted_at' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'accepted_at' => now(),
        ]);
    }
}
