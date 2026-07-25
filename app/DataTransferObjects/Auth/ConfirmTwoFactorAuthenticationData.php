<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

final readonly class ConfirmTwoFactorAuthenticationData
{
    public function __construct(
        public string $code,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
        );
    }
}
