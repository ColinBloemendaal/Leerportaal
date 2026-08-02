<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\ResellerKlant;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ResellerKlantRepository
{
    /**
     * @return LengthAwarePaginator<int, ResellerKlant>
     */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?ResellerKlant;

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
