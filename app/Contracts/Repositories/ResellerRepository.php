<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Reseller;

interface ResellerRepository
{
    public function findActiveBySlug(string $slug): ?Reseller;
}
