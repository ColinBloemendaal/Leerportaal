<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\FailedJob;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;

interface FailedJobRepository
{
    public function count(): int;

    /**
     * Failures since a given point in time -- the closest proxy this
     * codebase has to an "error rate" without a Sentry API client
     * (Sentry is currently write-only here, for reporting, not querying
     * back). See PlatformHealthService.
     */
    public function countSince(DateTimeInterface $since): int;

    /**
     * @return Collection<int, FailedJob>
     */
    public function recent(int $limit): Collection;
}
