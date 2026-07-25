<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Custom domain CNAME target
    |--------------------------------------------------------------------------
    |
    | Reseller custom domains must CNAME to this host. Placeholder until the
    | production hostname is known -- see docs/deployment.md.
    |
    */
    'custom_domain_target' => env('CUSTOM_DOMAIN_TARGET', 'tenants.leerportaal.test'),

];
