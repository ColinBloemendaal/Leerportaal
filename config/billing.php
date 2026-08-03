<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Storage overage rate
    |--------------------------------------------------------------------------
    |
    | Cents charged per gigabyte over the 5 GB included per reseller
    | (CLAUDE.md §11). Placeholder commercial default -- set the real rate
    | via STORAGE_OVERAGE_CENTS_PER_GB before this is used to bill anyone.
    |
    */
    'storage_overage_cents_per_gb' => env('STORAGE_OVERAGE_CENTS_PER_GB', 100),

    /*
    |--------------------------------------------------------------------------
    | VAT
    |--------------------------------------------------------------------------
    |
    | The platform's own home country (App\Services\Billing\VatCalculator
    | charges standard-rate VAT to any reseller in this country, and
    | applies the EU reverse-charge / outside-EU rules to every other
    | country) and the standard VAT rate to apply. The Netherlands/21% are
    | the actual current legal rate for this jurisdiction, not invented --
    | update both if the platform's legal seat or the rate itself changes.
    |
    */
    'platform_country_code' => env('BILLING_PLATFORM_COUNTRY_CODE', 'NL'),
    'vat_rate_percent' => env('BILLING_VAT_RATE_PERCENT', 21),

];
