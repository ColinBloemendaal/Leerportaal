<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerRepository;
use App\Enums\ResellerStatus;
use App\Models\Reseller;
use Illuminate\Support\LazyCollection;

final class EloquentResellerRepository implements ResellerRepository
{
    public function findActiveBySlug(string $slug): ?Reseller
    {
        return Reseller::query()
            ->where('slug', $slug)
            ->where('status', ResellerStatus::Active)
            ->first();
    }

    public function all(): LazyCollection
    {
        return Reseller::query()->cursor();
    }
}
