<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\ResellerKlant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

/**
 * @extends FilterablePaginator<ResellerKlant>
 */
interface ResellerKlantRepository extends FilterablePaginator
{
    /**
     * @return LengthAwarePaginator<int, ResellerKlant>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?ResellerKlant;

    /**
     * Every klant across every reseller -- platform-context iteration for
     * scheduled jobs that operate tenant-by-tenant (e.g. progress report
     * emails), not a tenant-facing read.
     *
     * @return LazyCollection<int, ResellerKlant>
     */
    public function all(): LazyCollection;

    /**
     * Null if the user has no resellerklant_id at all (platform staff, or
     * reseller staff not tied to a klant) -- for the klant dashboard's
     * "which klant is this user's own" self-service lookup.
     */
    public function findOwnKlant(User $user): ?ResellerKlant;

    /**
     * @return Collection<int, ResellerKlant>
     */
    public function trashed(): Collection;

    public function findTrashedById(int $id): ?ResellerKlant;
}
