<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends FilterablePaginator<Course>
 */
interface CourseRepository extends FilterablePaginator
{
    /**
     * Every platform (catalog) course plus the current reseller's own --
     * never another reseller's. Course has no TenantScope, same reasoning
     * as CourseCategory -- see App\Models\Course.
     *
     * @return Collection<int, Course>
     */
    public function visibleToCurrentReseller(): Collection;

    /**
     * Same visibility scope as visibleToCurrentReseller(), with
     * search/sort/filter for the reseller admin courses index --
     * deliberately not additionally gated by CourseAccessChecker, which
     * governs assignment *eligibility* (can this catalog course be
     * granted to a resellerklant), not browsing visibility. Keeping this
     * consistent with every other mixed-ownership repository's own
     * visibility rule (Media, Question, CourseCategory) rather than
     * inventing a narrower one just for this index.
     *
     * @return LengthAwarePaginator<int, Course>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator;
}
