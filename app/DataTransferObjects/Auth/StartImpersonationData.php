<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Auth;

final readonly class StartImpersonationData
{
    public function __construct(
        public int $impersonatorUserId,
        public int $targetUserId,
        public string $reason,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            impersonatorUserId: $data['impersonator_user_id'],
            targetUserId: $data['target_user_id'],
            reason: $data['reason'],
        );
    }
}
