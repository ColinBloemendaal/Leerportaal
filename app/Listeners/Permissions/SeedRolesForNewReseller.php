<?php

declare(strict_types=1);

namespace App\Listeners\Permissions;

use App\Actions\Permissions\SeedRolesForReseller;
use App\Events\Permissions\ResellerCreated;

final readonly class SeedRolesForNewReseller
{
    public function __construct(private SeedRolesForReseller $seedRoles) {}

    public function handle(ResellerCreated $event): void
    {
        ($this->seedRoles)($event->reseller);
    }
}
