<?php

declare(strict_types=1);

namespace App\Events\Tenancy;

use App\Models\CustomDomain;

final class CustomDomainVerified
{
    public function __construct(public readonly CustomDomain $customDomain) {}
}
