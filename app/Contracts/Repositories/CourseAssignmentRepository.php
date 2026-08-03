<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\CourseAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

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
     * Every active, deadlined assignment across every reseller --
     * platform-context iteration for the daily deadline/overdue reminder
     * command, deliberately bypassing the tenant scope (documented on the
     * implementation), same reasoning as ResellerKlantRepository::all().
     *
     * @return LazyCollection<int, CourseAssignment>
     */
    public function dueForDeadlineEvaluation(): LazyCollection;

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
