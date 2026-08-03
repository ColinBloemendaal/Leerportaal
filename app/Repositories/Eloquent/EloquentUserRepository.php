<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\User;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentUserRepository implements UserRepository
{
    public function __construct(private readonly QueryFilterApplier $filters) {}

    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $spec = new FilterableSpec(
            searchableColumns: ['name', 'email'],
            allowedSorts: ['name', 'email', 'created_at'],
            allowedFilters: ['reseller_id', 'platform_role'],
            defaultSort: 'name',
        );

        return $this->filters->apply(User::query()->with('reseller'), $filters, $spec)->paginate($perPage);
    }
}
