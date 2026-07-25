<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

final readonly class ForgotPasswordData
{
    public function __construct(
        public string $email,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
        );
    }
}
