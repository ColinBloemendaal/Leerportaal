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

];
