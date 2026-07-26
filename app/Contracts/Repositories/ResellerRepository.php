<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Reseller;
use Illuminate\Support\LazyCollection;

interface ResellerRepository
{
    public function findActiveBySlug(string $slug): ?Reseller;

    /**
     * @return LazyCollection<int, Reseller>
     */
    public function all(): LazyCollection;
}
