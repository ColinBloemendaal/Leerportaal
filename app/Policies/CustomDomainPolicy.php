<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CustomDomain;
use App\Models\User;

/**
 * Placeholder until roles exist (Phase 1 task 34): any reseller-side user
 * (not platform staff) can manage their own reseller's custom domains. No
 * controller consumes this yet -- the custom domain flow is backend-only
 * (Actions/Events) until a management UI exists. Tenant isolation itself
 * is the global scope's job, not the policy's -- see CLAUDE.md §3a.
 */
final class CustomDomainPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->reseller_id !== null;
    }

    public function create(User $user): bool
    {
        return $user->reseller_id !== null;
    }

    public function delete(User $user, CustomDomain $domain): bool
    {
        return $user->reseller_id !== null && $user->reseller_id === $domain->reseller_id;
    }
}
