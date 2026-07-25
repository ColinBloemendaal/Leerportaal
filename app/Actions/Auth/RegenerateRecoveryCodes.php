<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticator;

final readonly class RegenerateRecoveryCodes
{
    public function __construct(private TwoFactorAuthenticator $authenticator) {}

    /**
     * @return array<int, string>
     */
    public function __invoke(User $user): array
    {
        $codes = $this->authenticator->generateRecoveryCodes();

        $user->forceFill(['two_factor_recovery_codes' => $codes])->save();

        return $codes;
    }
}
