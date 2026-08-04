<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Reseller;
use App\Models\User;

/**
 * Placeholder until roles exist (Phase 1 task 34): any platform staff
 * (no reseller_id) can manage resellers -- the inverse check of the
 * reseller-side policies, since managing the resellers themselves is a
 * platform concern. No controller consumes this yet (Reseller CRUD is
 * Phase 6, admin panel); super-admin bypasses this via Gate::before
 * regardless.
 */
final class ResellerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->reseller_id === null;
    }

    public function view(User $user, Reseller $reseller): bool
    {
        return $user->reseller_id === null;
    }

    public function update(User $user, Reseller $reseller): bool
    {
        return $user->reseller_id === null;
    }

    /**
     * CLAUDE.md §8 (GDPR): "Per-reseller DPA acceptance and record." The
     * inverse boundary from every other method on this policy -- a
     * reseller-side user manages their *own* reseller's DPA, not
     * platform staff managing any reseller. Same boundary as
     * ResellerThemePolicy.
     */
    public function manageDpa(User $user, Reseller $reseller): bool
    {
        return $user->reseller_id === $reseller->id;
    }
}
