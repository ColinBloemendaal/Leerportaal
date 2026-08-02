<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

/**
 * Platform-wide aggregate counts for the platform admin dashboard.
 * Deliberately not scoped by any tenant -- this report spans every
 * reseller, which is exactly why it's an admin-only, platform-context
 * repository rather than something built through the ordinary
 * per-reseller repositories.
 */
interface PlatformDashboardRepository
{
    public function resellerCount(): int;

    public function userCount(): int;

    public function courseCount(): int;

    public function billedRevenueCents(): int;

    public function pendingRevenueCents(): int;
}
