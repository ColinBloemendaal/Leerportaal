<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Placeholder until roles exist (Phase 1 task 34) -- same pattern as
 * every other policy in this codebase. Tenant isolation is the global
 * scope's job, not this policy's -- see CLAUDE.md §3a.
 */
final class ResellerMailTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->reseller_id !== null;
    }

    public function update(User $user): bool
    {
        return $user->reseller_id !== null;
    }
}
