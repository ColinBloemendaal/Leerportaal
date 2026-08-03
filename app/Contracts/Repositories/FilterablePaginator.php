<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Every filterable-index repository (ResellerRepository, UserRepository,
 * ResellerKlantRepository, CourseRepository, CourseAssignmentRepository,
 * QuizAttemptRepository, ActivityLogRepository) already implements this
 * exact signature -- formalized as its own contract since they share it
 * by convention. Each implementer specializes TModel via
 * `@extends FilterablePaginator<ItsModel>` -- Laravel's
 * LengthAwarePaginator isn't declared @template-covariant, so this
 * (rather than a bare, unparameterized return type) is what keeps each
 * implementer's own more specific LengthAwarePaginator<int, ItsModel>
 * return type compatible with this interface's.
 *
 * Deliberately not @template-covariant: PHPStan still rejects that
 * (TModel occurs in "invariant position" once nested inside
 * LengthAwarePaginator's own non-covariant TValue, regardless of where
 * FilterablePaginator's own TModel is used) -- so nothing may treat
 * "some FilterablePaginator<object>" as a single return type across
 * multiple differently-specialized implementers. See
 * ExportResourceRegistry, which needs exactly that and works around it
 * by resolving+calling paginate() inside its own match() instead of
 * returning a repository instance.
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
