<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\ResetPasswordData;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Auth\PasswordBroker;

final readonly class ResetPassword
{
    public function __construct(private PasswordBroker $broker) {}

    /**
     * Returns a PasswordBroker::* status constant.
     */
    public function __invoke(ResetPasswordData $data): string
    {
        return $this->broker->reset(
            [
                'email' => $data->email,
                'password' => $data->password,
                'password_confirmation' => $data->password,
                'token' => $data->token,
            ],
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();

                event(new PasswordReset($user));
            },
        );
    }
}
