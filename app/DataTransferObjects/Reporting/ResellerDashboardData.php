<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Reporting;

use App\Support\Money;

final readonly class ResellerDashboardData
{
    public function __construct(
        public int $klantCount,
        public int $cursistCount,
        public int $assignmentCount,
        public Money $billedSpend,
        public Money $pendingSpend,
    ) {}
}
