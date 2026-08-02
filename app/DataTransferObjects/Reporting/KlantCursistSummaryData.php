<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Reporting;

final readonly class KlantCursistSummaryData
{
    public function __construct(
        public int $userId,
        public string $name,
        public int $assignedCount,
        public int $inProgressCount,
        public int $completedCount,
    ) {}
}
