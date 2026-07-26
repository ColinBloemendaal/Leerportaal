<?php

declare(strict_types=1);

use App\Models\ResellerTheme;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates reseller themes by reseller', function (): void {
    $this->assertTenantIsolated(ResellerTheme::class);
});
