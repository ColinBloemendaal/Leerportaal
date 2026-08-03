<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\FailedJobRepository;
use App\Models\FailedJob;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

final class EloquentFailedJobRepository implements FailedJobRepository
{
    public function count(): int
    {
        return FailedJob::query()->count();
    }

    public function countSince(DateTimeInterface $since): int
    {
        return FailedJob::query()->where('failed_at', '>=', $since)->count();
    }

    public function recent(int $limit): Collection
    {
        return FailedJob::query()->orderByDesc('failed_at')->limit($limit)->get();
    }
}
