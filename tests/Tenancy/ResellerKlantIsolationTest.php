<?php

declare(strict_types=1);

use App\Models\ResellerKlant;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates resellerklanten by reseller', function (): void {
    $this->assertTenantIsolated(ResellerKlant::class);
});
