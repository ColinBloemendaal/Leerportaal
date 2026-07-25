<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Placeholder until roles exist (Phase 1 task 34): any reseller-side user
 * (not platform staff) can manage resellerklanten. Tenant isolation itself
 * is the global scope's job, not the policy's -- see CLAUDE.md §3a.
 */
final class ResellerKlantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->reseller_id !== null;
    }

    public function create(User $user): bool
    {
        return $user->reseller_id !== null;
    }
}
