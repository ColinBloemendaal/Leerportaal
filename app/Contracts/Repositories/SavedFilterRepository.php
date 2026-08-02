<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\FilterableResource;
use App\Models\SavedFilter;
use Illuminate\Support\Collection;

interface SavedFilterRepository
{
    /**
     * @return Collection<int, SavedFilter>
     */
    public function forUserAndResource(int $userId, FilterableResource $resource): Collection;

    public function findOwnById(int $userId, int $id): ?SavedFilter;
}
