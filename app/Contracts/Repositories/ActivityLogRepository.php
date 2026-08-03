<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

interface ActivityLogRepository
{
    /**
     * Platform-wide, spanning every reseller -- the activity log itself
     * has no reseller_id to scope by (it records actions against every
     * kind of subject, tenant-owned or not).
     *
     * @return LengthAwarePaginator<int, Activity>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 25): LengthAwarePaginator;
}
