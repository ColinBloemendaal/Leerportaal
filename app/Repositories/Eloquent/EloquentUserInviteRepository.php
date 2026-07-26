<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserInviteRepository;
use App\Models\UserInvite;
use Illuminate\Support\Collection;

final class EloquentUserInviteRepository implements UserInviteRepository
{
    /**
     * @return Collection<int, UserInvite>
     */
    public function pendingForCurrentReseller(): Collection
    {
        return UserInvite::query()
            ->whereNull('accepted_at')
            ->orderByDesc('created_at')
            ->get();
    }

    public function findPendingById(int $id): ?UserInvite
    {
        // Relies on the caller having already resolved TenantContext to the
        // invite's own reseller (the accept route carries the reseller slug
        // for exactly this reason) -- a normal scoped read, not a bypass.
        return UserInvite::query()->whereKey($id)->whereNull('accepted_at')->first();
    }

    public function hasPendingInviteForEmail(string $email): bool
    {
        return UserInvite::query()->where('email', $email)->whereNull('accepted_at')->exists();
    }

    /**
     * @return Collection<int, UserInvite>
     */
    public function revokedForCurrentReseller(): Collection
    {
        return UserInvite::onlyTrashed()->orderByDesc('deleted_at')->get();
    }

    public function findRevokedById(int $id): ?UserInvite
    {
        return UserInvite::onlyTrashed()->whereKey($id)->first();
    }
}
