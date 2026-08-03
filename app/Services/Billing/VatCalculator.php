<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\DataTransferObjects\Billing\VatCalculationResult;
use App\Support\Billing\VatIdValidator;

/**
 * CLAUDE.md §11 doesn't specify exact VAT rates/rules (unlike the course
 * pricing formula) -- the standard rate and the platform's own home
 * country are config-driven (billing.vat_rate_percent /
 * billing.platform_country_code), not invented here. What *is* settled by
 * actual EU VAT law and encoded directly: a reseller in the platform's own
 * country pays standard-rate VAT; a reseller in another EU country with a
 * validly-formatted VAT ID gets the reverse charge (0%, buyer
 * self-accounts); a reseller in another EU country with no valid VAT ID
 * still pays standard-rate VAT (can't reverse-charge without one); a
 * reseller outside the EU is outside the scope of EU VAT entirely.
 */
final readonly class VatCalculator
{
    /**
     * @var list<string>
     */
    private const EU_COUNTRY_CODES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
        'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
        'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
    ];

    public function calculate(int $subtotalCents, ?string $resellerCountryCode, ?string $vatId): VatCalculationResult
    {
        $platformCountryCode = (string) config('billing.platform_country_code');

        if ($resellerCountryCode === null || $resellerCountryCode === $platformCountryCode) {
            return $this->standardRate($subtotalCents);
        }

        if (! in_array($resellerCountryCode, self::EU_COUNTRY_CODES, true)) {
            // Outside the EU: outside the scope of EU VAT.
            return VatCalculationResult::reverseCharged();
        }

        if ($vatId !== null && VatIdValidator::isValidFormat($vatId, $resellerCountryCode)) {
            return VatCalculationResult::reverseCharged();
        }

        return $this->standardRate($subtotalCents);
    }

    private function standardRate(int $subtotalCents): VatCalculationResult
    {
        $ratePercent = (int) config('billing.vat_rate_percent');

        return new VatCalculationResult(
            ratePercent: $ratePercent,
            vatCents: (int) round($subtotalCents * $ratePercent / 100),
            reverseCharge: false,
        );
    }
}
