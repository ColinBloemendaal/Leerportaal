<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Invites;

use App\Enums\Role;

final readonly class InviteUserData
{
    public function __construct(
        public string $name,
        public string $email,
        public Role $role,
        public ?int $resellerklantId,
        public int $invitedByUserId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            role: $data['role'] instanceof Role ? $data['role'] : Role::from($data['role']),
            resellerklantId: $data['resellerklant_id'] ?? null,
            invitedByUserId: $data['invited_by_user_id'],
        );
    }
}
