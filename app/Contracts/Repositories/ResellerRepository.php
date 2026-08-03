<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\Reseller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

/**
 * @extends FilterablePaginator<Reseller>
 */
interface ResellerRepository extends FilterablePaginator
{
    public function findActiveBySlug(string $slug): ?Reseller;

    /**
     * @return LazyCollection<int, Reseller>
     */
    public function all(): LazyCollection;

    /**
     * For the platform admin resellers index -- search/sort/filter via
     * QueryFilterApplier, see App\Support\Filtering.
     *
     * @return LengthAwarePaginator<int, Reseller>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator;
}
