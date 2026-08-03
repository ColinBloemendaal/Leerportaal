<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Every filterable-index repository (ResellerRepository, UserRepository,
 * ResellerKlantRepository, CourseRepository, CourseAssignmentRepository,
 * QuizAttemptRepository, ActivityLogRepository) already implements this
 * exact signature -- formalized here as its own contract because
 * App\Jobs\GenerateExportJob needs to call paginate() generically across
 * whichever one App\Enums\FilterableResource points at, without knowing
 * which specific repository it is. Each implementer specializes TModel
 * via `@extends FilterablePaginator<ItsModel>` -- Laravel's
 * LengthAwarePaginator isn't declared @template-covariant, so this
 * (rather than a bare, unparameterized return type) is what keeps each
 * implementer's own more specific LengthAwarePaginator<int, ItsModel>
 * return type compatible with this interface's.
 *
 * @template TModel of object
 */
interface FilterablePaginator
{
    /**
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator;
}
