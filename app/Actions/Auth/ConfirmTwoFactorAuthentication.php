<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\ConfirmTwoFactorAuthenticationData;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticator;
use Illuminate\Validation\ValidationException;

final readonly class ConfirmTwoFactorAuthentication
{
    public function __construct(private TwoFactorAuthenticator $authenticator) {}

    public function __invoke(User $user, ConfirmTwoFactorAuthenticationData $data): void
    {
        if ($user->two_factor_secret === null || ! $this->authenticator->verify($user->two_factor_secret, $data->code)) {
            throw ValidationException::withMessages(['code' => trans('auth.failed')]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
    }
}
