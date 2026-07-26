<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Reseller;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Null reseller_id: platform staff, not tied to any reseller.
     */
    public function platformStaff(): static
    {
        return $this->state(fn (array $attributes): array => [
            'reseller_id' => null,
            'resellerklant_id' => null,
        ]);
    }

    /**
     * Implies platformStaff(): platform_role only makes sense with no
     * reseller_id -- see App\Enums\Role's class docblock.
     */
    public function platformRole(Role $role = Role::SuperAdmin): static
    {
        return $this->platformStaff()->state(fn (array $attributes): array => [
            'platform_role' => $role,
        ]);
    }

    public function twoFactorEnabled(): static
    {
        return $this->state(function (array $attributes): array {
            $authenticator = app(TwoFactorAuthenticator::class);

            return [
                'two_factor_secret' => $authenticator->generateSecretKey(),
                'two_factor_recovery_codes' => $authenticator->generateRecoveryCodes(),
                'two_factor_confirmed_at' => now(),
            ];
        });
    }
}
