<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\UserInvite;
use Illuminate\Support\Collection;

interface UserInviteRepository
{
    /**
     * @return Collection<int, UserInvite>
     */
    public function pendingForCurrentReseller(): Collection;

    /**
     * Null if the invite doesn't exist, was revoked (soft-deleted), or has
     * already been accepted -- callers don't need to distinguish why.
     */
    public function findPendingById(int $id): ?UserInvite;

    public function hasPendingInviteForEmail(string $email): bool;

    /**
     * @return Collection<int, UserInvite>
     */
    public function revokedForCurrentReseller(): Collection;

    public function findRevokedById(int $id): ?UserInvite;
}
