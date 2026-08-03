<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Contracts\Storage\StorageMetering;

/**
 * CLAUDE.md §11: "Storage: 5 GB included per reseller, metered beyond
 * that." The per-GB rate itself isn't specified anywhere in CLAUDE.md
 * (unlike the course-pricing formula, which is exact) -- it's a genuine
 * commercial decision, not an engineering one, so it's config-driven
 * (billing.storage_overage_cents_per_gb) rather than a value invented
 * here. The shipped default is a clearly-labelled placeholder; set the
 * real commercial rate via STORAGE_OVERAGE_CENTS_PER_GB before billing
 * anyone for real.
 */
final readonly class StorageOverageCalculator
{
    public function __construct(private StorageMetering $storage) {}

    public function overageChargeCents(int $resellerId): int
    {
        $overageBytes = max(0, $this->storage->usageBytes($resellerId) - $this->storage->includedBytes());

        if ($overageBytes === 0) {
            return 0;
        }

        $overageGigabytes = (int) ceil($overageBytes / (1024 * 1024 * 1024));

        return $overageGigabytes * (int) config('billing.storage_overage_cents_per_gb');
    }
}
