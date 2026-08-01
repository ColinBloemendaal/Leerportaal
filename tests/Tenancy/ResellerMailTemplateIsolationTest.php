<?php

declare(strict_types=1);

use App\Models\ResellerMailTemplate;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates reseller mail templates by reseller', function (): void {
    $this->assertTenantIsolated(ResellerMailTemplate::class);
});
