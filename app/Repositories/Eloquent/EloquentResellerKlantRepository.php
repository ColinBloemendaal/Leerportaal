<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerKlantRepository;
use App\Models\ResellerKlant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class EloquentResellerKlantRepository implements ResellerKlantRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return ResellerKlant::query()
            ->when($search !== null, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findById(int $id): ?ResellerKlant
    {
        return ResellerKlant::query()->find($id);
    }

    public function trashed(): Collection
    {
        return ResellerKlant::onlyTrashed()->orderByDesc('deleted_at')->get();
    }

    public function findTrashedById(int $id): ?ResellerKlant
    {
        return ResellerKlant::onlyTrashed()->whereKey($id)->first();
    }
}
