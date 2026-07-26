<?php

declare(strict_types=1);

namespace App\Events\Permissions;

use App\Models\Reseller;

final class ResellerCreated
{
    public function __construct(public readonly Reseller $reseller) {}
}
