<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerKlantRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

final class EloquentResellerKlantRepository implements ResellerKlantRepository
{
    public function __construct(private readonly QueryFilterApplier $filters) {}

    public function all(): LazyCollection
    {
        // ResellerKlant is TenantScoped -- this deliberately bypasses that
        // for platform-context iteration across every reseller, see the
        // interface docblock.
        return ResellerKlant::query()->withoutTenantScope()->cursor();
    }

    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $spec = new FilterableSpec(
            searchableColumns: ['name'],
            allowedSorts: ['name', 'created_at'],
            defaultSort: 'name',
        );

        return $this->filters->apply(ResellerKlant::query(), $filters, $spec)->paginate($perPage);
    }

    public function findById(int $id): ?ResellerKlant
    {
        return ResellerKlant::query()->find($id);
    }

    public function findOwnKlant(User $user): ?ResellerKlant
    {
        return $user->resellerklant_id === null ? null : $this->findById($user->resellerklant_id);
    }

    public function trashed(): Collection
    {
        return ResellerKlant::onlyTrashed()->orderByDesc('deleted_at')->get();
    }

    public function findTrashedById(int $id): ?ResellerKlant
    {
        return ResellerKlant::onlyTrashed()->whereKey($id)->first();
    }
}
