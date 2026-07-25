<?php

declare(strict_types=1);

use App\Models\CustomDomain;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates custom domains by reseller', function (): void {
    $this->assertTenantIsolated(CustomDomain::class);
});
