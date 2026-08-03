<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Reporting;

final readonly class QueueWorkloadData
{
    public function __construct(
        public string $name,
        public int $length,
        public int $waitSeconds,
        public int $processes,
    ) {}
}
