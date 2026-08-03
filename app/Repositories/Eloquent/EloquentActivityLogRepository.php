<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ActivityLogRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

final class EloquentActivityLogRepository implements ActivityLogRepository
{
    public function __construct(private readonly QueryFilterApplier $filters) {}

    public function paginate(FilterRequestData $filters, int $perPage = 25): LengthAwarePaginator
    {
        $spec = new FilterableSpec(
            searchableColumns: ['description'],
            allowedSorts: ['created_at', 'event'],
            allowedFilters: ['event', 'log_name', 'subject_type'],
            defaultSort: 'created_at',
        );

        return $this->filters
            ->apply(Activity::query()->with('causer'), $filters, $spec)
            ->paginate($perPage);
    }
}
