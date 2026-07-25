<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticator;

/**
 * Generates a new secret and recovery codes, unconfirmed until the user
 * verifies a code -- see ConfirmTwoFactorAuthentication.
 */
final readonly class EnableTwoFactorAuthentication
{
    public function __construct(private TwoFactorAuthenticator $authenticator) {}

    public function __invoke(User $user): User
    {
        $user->forceFill([
            'two_factor_secret' => $this->authenticator->generateSecretKey(),
            'two_factor_recovery_codes' => $this->authenticator->generateRecoveryCodes(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return $user->fresh() ?? $user;
    }
}
