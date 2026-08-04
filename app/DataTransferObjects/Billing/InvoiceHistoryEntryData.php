<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Billing;

use App\Support\Money;

final readonly class InvoiceHistoryEntryData
{
    public function __construct(
        public int $id,
        public string $periodStart,
        public string $periodEnd,
        public string $status,
        public Money $total,
        public ?string $issuedAt,
        public ?string $paidAt,
    ) {}
}
