<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\TwoFactorChallengeData;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticator;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;

final readonly class CompleteTwoFactorChallenge
{
    public function __construct(
        private StatefulGuard $guard,
        private TwoFactorAuthenticator $authenticator,
    ) {}

    public function __invoke(TwoFactorChallengeData $data): void
    {
        $userId = session('login.id');
        $user = is_int($userId) ? User::query()->whereKey($userId)->first() : null;

        if ($user === null || ! $this->verifyCode($user, $data->code)) {
            throw ValidationException::withMessages(['code' => trans('auth.failed')]);
        }

        $this->guard->login($user, (bool) session('login.remember', false));

        session()->forget(['login.id', 'login.remember']);
    }

    private function verifyCode(User $user, string $code): bool
    {
        if ($user->two_factor_secret !== null && $this->authenticator->verify($user->two_factor_secret, $code)) {
            return true;
        }

        return $this->consumeRecoveryCode($user, $code);
    }

    private function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        if (! in_array($code, $codes, true)) {
            return false;
        }

        $user->forceFill([
            'two_factor_recovery_codes' => array_values(array_diff($codes, [$code])),
        ])->save();

        return true;
    }
}
