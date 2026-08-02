<?php

declare(strict_types=1);

use App\Models\Group;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates groups by reseller', function (): void {
    $this->assertTenantIsolated(Group::class);
});
