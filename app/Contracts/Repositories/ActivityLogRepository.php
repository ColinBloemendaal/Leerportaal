<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

/**
 * @extends FilterablePaginator<Activity>
 */
interface ActivityLogRepository extends FilterablePaginator
{
    /**
     * Platform-wide, spanning every reseller -- the activity log itself
     * has no reseller_id to scope by (it records actions against every
     * kind of subject, tenant-owned or not).
     *
     * @return LengthAwarePaginator<int, Activity>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 25): LengthAwarePaginator;

    /**
     * Everything a user did (causer) and everything done to them
     * (subject) -- for the per-user detail page's timeline.
     *
     * @return Collection<int, Activity>
     */
    public function timelineForUser(int $userId): Collection;
}
