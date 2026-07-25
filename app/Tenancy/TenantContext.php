<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Reseller;

/**
 * The current tenant for this request. Bound as a scoped singleton
 * (App\Providers\AppServiceProvider) and populated once by
 * ResolveTenant middleware -- never resolve tenant from the request
 * inside a model or service. See CLAUDE.md §2.
 */
final class TenantContext
{
    private ?Reseller $reseller = null;

    public function set(Reseller $reseller): void
    {
        $this->reseller = $reseller;
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
