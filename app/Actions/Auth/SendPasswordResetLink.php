<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\ForgotPasswordData;
use Illuminate\Contracts\Auth\PasswordBroker;

final readonly class SendPasswordResetLink
{
    public function __construct(private PasswordBroker $broker) {}

    /**
     * Returns a PasswordBroker::* status constant. Always returns
     * normally (never throws for "user not found") -- the caller shows a
     * generic message regardless, to avoid leaking which emails exist.
     */
    public function __invoke(ForgotPasswordData $data): string
    {
        return $this->broker->sendResetLink(['email' => $data->email]);
    }
}
