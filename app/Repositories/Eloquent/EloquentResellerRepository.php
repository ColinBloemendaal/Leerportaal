<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\ResellerStatus;
use App\Models\Reseller;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

final class EloquentResellerRepository implements ResellerRepository
{
    public function __construct(private readonly QueryFilterApplier $filters) {}

    public function findActiveBySlug(string $slug): ?Reseller
    {
        return Reseller::query()
            ->where('slug', $slug)
            ->where('status', ResellerStatus::Active)
            ->first();
    }

    public function all(): LazyCollection
    {
        return Reseller::query()->cursor();
    }

    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $spec = new FilterableSpec(
            searchableColumns: ['name', 'slug'],
            allowedSorts: ['name', 'status', 'created_at'],
            allowedFilters: ['status'],
            defaultSort: 'name',
        );

        return $this->filters->apply(Reseller::query(), $filters, $spec)->paginate($perPage);
    }
}
