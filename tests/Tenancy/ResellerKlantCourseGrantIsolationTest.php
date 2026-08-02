<?php

declare(strict_types=1);

use App\Models\ResellerKlantCourseGrant;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates resellerklant course grants by reseller', function (): void {
    $this->assertTenantIsolated(ResellerKlantCourseGrant::class);
});
