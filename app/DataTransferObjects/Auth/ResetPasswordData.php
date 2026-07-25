<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

final readonly class ResetPasswordData
{
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token'],
            email: $data['email'],
            password: $data['password'],
        );
    }
}
