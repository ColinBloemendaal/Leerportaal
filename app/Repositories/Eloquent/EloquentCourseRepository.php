<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CourseRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\Course;
use App\Support\Filtering\QueryFilterApplier;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentCourseRepository implements CourseRepository
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly QueryFilterApplier $filters,
    ) {}

    public function visibleToCurrentReseller(): Collection
    {
        return $this->visibilityScope()->get();
    }

    public function findById(int $id): ?Course
    {
        return Course::query()->find($id);
    }

    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $spec = new FilterableSpec(
            searchableColumns: ['title'],
            allowedSorts: ['status', 'created_at'],
            allowedFilters: ['status'],
            defaultSort: 'created_at',
        );

        return $this->filters->apply($this->visibilityScope(), $filters, $spec)->paginate($perPage);
    }

    /**
     * @return Builder<Course>
     */
    private function visibilityScope(): Builder
    {
        $tenantId = $this->tenantContext->id();

        return Course::query()
            ->where(function (Builder $query) use ($tenantId) {
                $query->whereNull('reseller_id')->orWhere('reseller_id', $tenantId);
            });
    }
}
