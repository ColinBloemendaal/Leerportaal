<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Contracts\Repositories\MediaRepository;
use App\Contracts\Repositories\PlatformDashboardRepository;
use App\Contracts\Storage\StorageMetering;
use App\DataTransferObjects\Reporting\PlatformDashboardData;
use App\Support\Money;

final readonly class PlatformDashboardService
{
    public function __construct(
        private PlatformDashboardRepository $dashboard,
        private MediaRepository $media,
        private StorageMetering $storage,
    ) {}

    public function snapshot(): PlatformDashboardData
    {
        $resellerCount = $this->dashboard->resellerCount();

        return new PlatformDashboardData(
            resellerCount: $resellerCount,
            userCount: $this->dashboard->userCount(),
            courseCount: $this->dashboard->courseCount(),
            billedRevenue: Money::fromCents($this->dashboard->billedRevenueCents()),
            pendingRevenue: Money::fromCents($this->dashboard->pendingRevenueCents()),
            storageUsedBytes: $this->media->totalBytesAcrossPlatform(),
            // 5 GB included per reseller (CLAUDE.md §11) -- the platform
            // total capacity is that allowance times how many resellers
            // exist, not a fixed constant.
            storageIncludedBytes: $this->storage->includedBytes() * $resellerCount,
        );
    }
}
