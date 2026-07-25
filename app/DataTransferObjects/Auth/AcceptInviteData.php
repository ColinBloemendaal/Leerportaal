<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

final readonly class AcceptInviteData
{
    public function __construct(
        public int $inviteId,
        public string $hash,
        public string $password,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            inviteId: $data['invite_id'],
            hash: $data['hash'],
            password: $data['password'],
        );
    }
}
