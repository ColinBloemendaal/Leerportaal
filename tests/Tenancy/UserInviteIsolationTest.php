<?php

declare(strict_types=1);

use App\Models\UserInvite;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates user invites by reseller', function (): void {
    $this->assertTenantIsolated(UserInvite::class);
});
