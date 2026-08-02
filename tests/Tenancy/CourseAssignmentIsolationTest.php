<?php

declare(strict_types=1);

use App\Models\CourseAssignment;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates course assignments by reseller', function (): void {
    $this->assertTenantIsolated(CourseAssignment::class);
});
