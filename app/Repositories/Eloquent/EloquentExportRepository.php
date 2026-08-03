<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ExportRepository;
use App\Models\Export;
use Illuminate\Database\Eloquent\Collection;

final class EloquentExportRepository implements ExportRepository
{
    public function findById(int $id): ?Export
    {
        return Export::query()->find($id);
    }

    public function findOwnById(int $userId, int $id): ?Export
    {
        return Export::query()->where('user_id', $userId)->find($id);
    }

    public function forUser(int $userId): Collection
    {
        return Export::query()->where('user_id', $userId)->orderByDesc('created_at')->get();
    }
}
