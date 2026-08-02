<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Contracts\Repositories\MediaRepository;
use App\Contracts\Storage\StorageMetering;

final readonly class EloquentStorageMeteringService implements StorageMetering
{
    private const INCLUDED_BYTES = 5 * 1024 * 1024 * 1024;

    public function __construct(private MediaRepository $media) {}

    public function usageBytes(int $resellerId): int
    {
        return $this->media->totalBytesForReseller($resellerId);
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
