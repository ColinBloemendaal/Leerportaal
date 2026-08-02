<?php

declare(strict_types=1);

namespace App\Contracts\Storage;

/**
 * Mandatory interface per CLAUDE.md §3a's list -- swappable, and lets
 * tests simulate a reseller's usage without actually uploading
 * gigabytes of media. See Tests\Fakes\FakeStorageMetering.
 */
interface StorageMetering
{
    public function usageBytes(int $resellerId): int;

    /**
     * 5 GB included per reseller -- CLAUDE.md §11.
     */
    public function includedBytes(): int;

    public function isOverLimit(int $resellerId): bool;
}
