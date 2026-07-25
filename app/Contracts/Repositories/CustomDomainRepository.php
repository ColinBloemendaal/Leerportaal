<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\CustomDomain;

interface CustomDomainRepository
{
    public function findVerifiedByDomain(string $domain): ?CustomDomain;
}
