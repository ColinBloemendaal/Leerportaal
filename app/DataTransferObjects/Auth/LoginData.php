<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            remember: (bool) ($data['remember'] ?? false),
        );
    }
}
