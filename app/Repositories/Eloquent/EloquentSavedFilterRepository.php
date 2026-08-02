<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SavedFilterRepository;
use App\Enums\FilterableResource;
use App\Models\SavedFilter;
use Illuminate\Support\Collection;

final class EloquentSavedFilterRepository implements SavedFilterRepository
{
    public function forUserAndResource(int $userId, FilterableResource $resource): Collection
    {
        return SavedFilter::query()
            ->where('user_id', $userId)
            ->where('resource_type', $resource)
            ->orderBy('name')
            ->get();
    }

    public function findOwnById(int $userId, int $id): ?SavedFilter
    {
        return SavedFilter::query()->where('user_id', $userId)->find($id);
    }
}
