<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerKlantRepository;
use App\Models\ResellerKlant;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentResellerKlantRepository implements ResellerKlantRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return ResellerKlant::query()
            ->when($search !== null, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage);
    }
}
