<?php

declare(strict_types=1);

use App\Models\Page;
use Tests\Tenancy\InteractsWithTenancy;

uses(InteractsWithTenancy::class);

it('isolates pages by reseller', function (): void {
    $this->assertTenantIsolated(Page::class);
});
