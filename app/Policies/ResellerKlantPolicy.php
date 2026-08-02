<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ResellerKlant;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

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

    public function delete(User $user, ResellerKlant $klant): bool
    {
        return $user->reseller_id !== null && $user->reseller_id === $klant->reseller_id;
    }

    public function restore(User $user, ResellerKlant $klant): bool
    {
        return $this->delete($user, $klant);
    }

    /**
     * Unlike the placeholders above, this one uses real roles -- it's
     * new, not legacy code written before roles existed. Reseller-side
     * staff (no resellerklant_id of their own) manage every klant within
     * their reseller; a klant-admin/klant-manager may only view their
     * own klant, not another one within the same reseller. Same
     * role-check pattern as UserPolicy::impersonate().
     */
    public function view(User $user, ResellerKlant $klant): bool
    {
        if ($user->reseller_id !== $klant->reseller_id) {
            return false;
        }

        if ($user->resellerklant_id === null) {
            return true;
        }

        if ($user->resellerklant_id !== $klant->id) {
            return false;
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($user->reseller_id);

        return $user->hasRole(['klant-admin', 'klant-manager']);
    }
}
