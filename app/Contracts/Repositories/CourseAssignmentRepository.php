<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\CourseAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends FilterablePaginator<CourseAssignment>
 */
interface CourseAssignmentRepository extends FilterablePaginator
{
    /**
     * Explicitly parameterized by user id, not ambient Auth::user(), so
     * this works the same whether called from a request or a queued job.
     *
     * @return Collection<int, CourseAssignment>
     */
    public function forUser(int $userId): Collection;

    /**
     * For the reseller admin assignments index -- CourseAssignment is
     * TenantScoped, so this is already scoped to the current reseller
     * with no explicit filtering needed. No free-text search: it has no
     * searchable column of its own (course title/cursist name live on
     * related tables, out of scope for QueryFilterApplier's single-table
     * design).
     *
     * @return LengthAwarePaginator<int, CourseAssignment>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator;
}
