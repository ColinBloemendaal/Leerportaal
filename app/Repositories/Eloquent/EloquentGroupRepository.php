<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\GroupRepository;
use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;

final class EloquentGroupRepository implements GroupRepository
{
    public function forCurrentReseller(): Collection
    {
        return Group::query()->orderBy('name')->get();
    }

    public function findForCurrentReseller(int $id): ?Group
    {
        return Group::query()->find($id);
    }
}
