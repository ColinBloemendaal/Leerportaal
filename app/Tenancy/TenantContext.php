<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Reseller;
use Spatie\Permission\PermissionRegistrar;

/**
 * The current tenant for this request. Bound as a scoped singleton
 * (App\Providers\AppServiceProvider) and populated once by
 * ResolveTenant middleware -- never resolve tenant from the request
 * inside a model or service. See CLAUDE.md §2.
 */
final class TenantContext
{
    private ?Reseller $reseller = null;

    public function __construct(private readonly PermissionRegistrar $permissionRegistrar) {}

    public function set(Reseller $reseller): void
    {
        $this->reseller = $reseller;

        // Keeps spatie/laravel-permission's teams feature (team_foreign_key
        // = reseller_id) in sync with every call site that resolves tenant
        // -- not just ResolveTenant middleware -- so role/permission checks
        // never need a separate "which reseller" step of their own.
        $this->permissionRegistrar->setPermissionsTeamId($reseller->id);
    }

    public function get(): ?Reseller
    {
        return $this->reseller;
    }

    public function id(): ?int
    {
        return $this->reseller?->id;
    }

    public function check(): bool
    {
        return $this->reseller !== null;
    }
}
