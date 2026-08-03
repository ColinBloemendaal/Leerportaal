<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Billing;

final readonly class VatCalculationResult
{
    public function __construct(
        public int $ratePercent,
        public int $vatCents,
        public bool $reverseCharge,
    ) {}

    public static function reverseCharged(): self
    {
        return new self(ratePercent: 0, vatCents: 0, reverseCharge: true);
    }
}
