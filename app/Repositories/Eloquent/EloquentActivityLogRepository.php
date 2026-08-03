<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ActivityLogRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\User;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

final class EloquentActivityLogRepository implements ActivityLogRepository
{
    public function __construct(private readonly QueryFilterApplier $filters) {}

    public function paginate(FilterRequestData $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = Activity::query()->with('causer');

        // date_from/date_to/reseller_id aren't plain column equality, so
        // they're handled here rather than through QueryFilterApplier's
        // generic allowedFilters loop -- pulled out of the same
        // ?filter[x]=y bag the generic layer reads, then removed before
        // the rest of filters->filters gets applied normally below.
        $dateFrom = $filters->filters['date_from'] ?? null;
        $dateTo = $filters->filters['date_to'] ?? null;
        $resellerId = $filters->filters['reseller_id'] ?? null;

        if ($dateFrom !== null) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->where('created_at', '<=', $dateTo);
        }

        if ($resellerId !== null) {
            $query->whereHasMorph('causer', [User::class], function (Builder $causer) use ($resellerId): void {
                $causer->where('reseller_id', $resellerId);
            });
        }

        $remainingFilters = new FilterRequestData(
            search: $filters->search,
            sort: $filters->sort,
            sortDirection: $filters->sortDirection,
            filters: array_diff_key($filters->filters, array_flip(['date_from', 'date_to', 'reseller_id'])),
        );

        $spec = new FilterableSpec(
            searchableColumns: ['description'],
            allowedSorts: ['created_at', 'event'],
            allowedFilters: ['event', 'log_name', 'subject_type', 'causer_id', 'subject_id'],
            defaultSort: 'created_at',
        );

        return $this->filters->apply($query, $remainingFilters, $spec)->paginate($perPage);
    }
}
