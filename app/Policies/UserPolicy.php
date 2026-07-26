<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

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
}
