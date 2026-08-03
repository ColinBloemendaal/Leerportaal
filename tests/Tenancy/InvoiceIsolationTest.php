<?php

declare(strict_types=1);

use App\Models\Invoice;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates invoices by reseller', function (): void {
    $this->assertTenantIsolated(Invoice::class);
});
