<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

/**
 * Aggregate counts for the reseller admin dashboard, scoped to the
 * current reseller via the ambient TenantContext -- unlike
 * PlatformDashboardRepository, this never needs to bypass the tenant
 * scope, since a reseller admin only ever sees their own numbers.
 */
interface ResellerDashboardRepository
{
    public function klantCount(): int;

    /**
     * Users belonging to a resellerklant (resellerklant_id set) --
     * cursisten, not the reseller's own staff.
     */
    public function cursistCount(): int;

    public function assignmentCount(): int;

    public function billedSpendCents(): int;

    public function pendingSpendCents(): int;
}
