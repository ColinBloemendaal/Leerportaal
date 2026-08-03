<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CourseAssignmentRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\CourseAssignment;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

final class EloquentCourseAssignmentRepository implements CourseAssignmentRepository
{
    public function __construct(private readonly QueryFilterApplier $filters) {}

    public function forUser(int $userId): Collection
    {
        return CourseAssignment::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->with('course')
            ->orderByDesc('assigned_at')
            ->get();
    }

    public function dueForDeadlineEvaluation(): LazyCollection
    {
        // Platform-wide by design -- see the interface docblock.
        return CourseAssignment::query()
            ->withoutTenantScope()
            ->whereNull('revoked_at')
            ->whereNotNull('deadline_at')
            ->with(['course', 'user', 'reseller'])
            ->cursor();
    }

    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $spec = new FilterableSpec(
            allowedSorts: ['assigned_at', 'deadline_at', 'billing_state'],
            allowedFilters: ['billing_state'],
            defaultSort: 'assigned_at',
        );

        return $this->filters
            ->apply(CourseAssignment::query()->with(['user', 'course']), $filters, $spec)
            ->paginate($perPage);
    }
}
