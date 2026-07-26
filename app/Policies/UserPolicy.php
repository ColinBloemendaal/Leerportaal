<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Placeholder until roles exist (Phase 1 task 34): a reseller-side user
 * can view/manage users within their own reseller (both reseller staff
 * and their klanten's cursisten, since both share reseller_id). Platform
 * staff aren't reachable through this policy at all -- they have no
 * reseller_id to scope by, and super-admin bypasses every policy via
 * Gate::before (App\Policies\SuperAdminBypass) regardless.
 */
final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->reseller_id !== null;
    }

    public function view(User $user, User $target): bool
    {
        return $user->reseller_id !== null && $user->reseller_id === $target->reseller_id;
    }

    public function update(User $user, User $target): bool
    {
        return $this->view($user, $target);
    }

    public function delete(User $user, User $target): bool
    {
        return $this->view($user, $target) && $user->isNot($target);
    }

    /**
     * Impersonation hierarchy (human decision, CLAUDE.md §7): anyone who
     * "owns" other users can impersonate them.
     * - super-admin: any reseller-side user, any reseller (also covered
     *   by Gate::before, but kept explicit here for a security-critical
     *   check -- don't rely solely on something else).
     * - reseller staff (reseller_id set, no resellerklant_id of their
     *   own): any user within their own reseller who has a
     *   resellerklant_id -- their klanten and cursisten, not fellow
     *   staff.
     * - klant-admin / klant-manager: cursisten within their own
     *   resellerklant only.
     * Platform staff can never be impersonated -- no privilege-escalation
     * path from impersonating another platform account.
     */
    public function impersonate(User $user, User $target): bool
    {
        if ($target->reseller_id === null || $user->is($target)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->reseller_id !== null && $user->resellerklant_id === null) {
            return $user->reseller_id === $target->reseller_id && $target->resellerklant_id !== null;
        }

        if ($user->resellerklant_id !== null && $user->resellerklant_id === $target->resellerklant_id) {
            // hasRole() checks against the ambient team id -- set it
            // explicitly rather than trusting TenantContext to already be
            // resolved for this request.
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->reseller_id);

            return $user->hasRole(['klant-admin', 'klant-manager']);
        }

        return false;
    }
}
