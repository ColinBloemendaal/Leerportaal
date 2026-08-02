<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Contracts\Storage\StorageMetering;

final class FakeStorageMetering implements StorageMetering
{
    private const INCLUDED_BYTES = 5 * 1024 * 1024 * 1024;

    /**
     * @var array<int, int>
     */
    private array $usage = [];

    public function setUsageBytes(int $resellerId, int $bytes): void
    {
        $this->usage[$resellerId] = $bytes;
    }

    public function usageBytes(int $resellerId): int
    {
        return $this->usage[$resellerId] ?? 0;
    }

    public function includedBytes(): int
    {
        return self::INCLUDED_BYTES;
    }

    public function isOverLimit(int $resellerId): bool
    {
        return $this->usageBytes($resellerId) > $this->includedBytes();
    }
}
