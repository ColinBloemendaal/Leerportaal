<?php

declare(strict_types=1);

namespace App\Actions\Permissions;

use App\Enums\Role;
use App\Models\Reseller;
use Spatie\Permission\Models\Role as RoleModel;

/**
 * Idempotent: safe to run for a reseller that already has some or all of
 * its roles (e.g. re-running the permissions:sync-roles backfill command).
 */
final readonly class SeedRolesForReseller
{
    public function __invoke(Reseller $reseller): void
    {
        foreach (Role::teamRoles() as $role) {
            RoleModel::query()->firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'web',
                'reseller_id' => $reseller->id,
            ]);
        }
    }
}
