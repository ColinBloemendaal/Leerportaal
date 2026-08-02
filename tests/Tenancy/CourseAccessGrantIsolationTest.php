<?php

declare(strict_types=1);

use App\Models\CourseAccessGrant;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates course access grants by reseller', function (): void {
    $this->assertTenantIsolated(CourseAccessGrant::class);
});
