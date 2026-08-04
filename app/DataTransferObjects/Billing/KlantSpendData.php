<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Billing;

use App\Support\Money;

final readonly class KlantSpendData
{
    public function __construct(
        public ?int $klantId,
        public string $klantName,
        public Money $subtotal,
    ) {}
}
