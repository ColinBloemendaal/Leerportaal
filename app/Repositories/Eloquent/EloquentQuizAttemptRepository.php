<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\QuizAttemptRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\QuizAttempt;
use App\Support\Filtering\QueryFilterApplier;
use App\Tenancy\TenantContext;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentQuizAttemptRepository implements QuizAttemptRepository
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly QueryFilterApplier $filters,
    ) {}

    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $tenantId = $this->tenantContext->id();

        $base = QuizAttempt::query()
            ->whereHas('user', fn ($query) => $query->where('reseller_id', $tenantId))
            ->with(['user', 'quiz']);

        $spec = new FilterableSpec(
            allowedSorts: ['started_at', 'submitted_at', 'score'],
            allowedFilters: ['passed'],
            defaultSort: 'started_at',
        );

        return $this->filters->apply($base, $filters, $spec)->paginate($perPage);
    }
}
