<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Reporting;

use App\Support\Money;

final readonly class PlatformDashboardData
{
    public function __construct(
        public int $resellerCount,
        public int $userCount,
        public int $courseCount,
        public Money $billedRevenue,
        public Money $pendingRevenue,
        public int $storageUsedBytes,
        public int $storageIncludedBytes,
    ) {}
}
