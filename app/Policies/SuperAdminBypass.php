<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Registered via Gate::before in AppServiceProvider. Returning null (not
 * false) for non-super-admins lets every other policy check run
 * normally -- only an explicit true short-circuits the gate.
 *
 * Lives under App\Policies rather than inline in the provider so the
 * User type-hint stays within the "who may touch Models" arch
 * whitelist -- AppServiceProvider itself never references User.
 */
final class SuperAdminBypass
{
    public function __invoke(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }
}
