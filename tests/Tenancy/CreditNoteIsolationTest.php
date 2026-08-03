<?php

declare(strict_types=1);

use App\Models\CreditNote;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates credit notes by reseller', function (): void {
    $this->assertTenantIsolated(CreditNote::class);
});
