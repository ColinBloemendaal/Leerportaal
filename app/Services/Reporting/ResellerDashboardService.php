<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Contracts\Repositories\ResellerDashboardRepository;
use App\DataTransferObjects\Reporting\ResellerDashboardData;
use App\Support\Money;

final readonly class ResellerDashboardService
{
    public function __construct(private ResellerDashboardRepository $dashboard) {}

    public function snapshot(): ResellerDashboardData
    {
        return new ResellerDashboardData(
            klantCount: $this->dashboard->klantCount(),
            cursistCount: $this->dashboard->cursistCount(),
            assignmentCount: $this->dashboard->assignmentCount(),
            billedSpend: Money::fromCents($this->dashboard->billedSpendCents()),
            pendingSpend: Money::fromCents($this->dashboard->pendingSpendCents()),
        );
    }
}
