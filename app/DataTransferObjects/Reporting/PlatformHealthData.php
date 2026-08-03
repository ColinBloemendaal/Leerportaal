<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Reporting;

final readonly class PlatformHealthData
{
    /**
     * @param  list<QueueWorkloadData>  $queues
     */
    public function __construct(
        public array $queues,
        public int $failedJobCount,
        public int $failedJobCountLast24Hours,
        public int $storageUsedBytes,
    ) {}
}
