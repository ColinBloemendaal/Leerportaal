<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Billing;

use App\Support\Money;

final readonly class ResellerBillingDashboardData
{
    /**
     * @param  list<KlantSpendData>  $klantBreakdown
     * @param  list<InvoiceHistoryEntryData>  $history
     */
    public function __construct(
        public ?string $currentPeriodStart,
        public ?string $currentPeriodEnd,
        public Money $currentPeriodSubtotal,
        public array $klantBreakdown,
        public array $history,
    ) {}
}
