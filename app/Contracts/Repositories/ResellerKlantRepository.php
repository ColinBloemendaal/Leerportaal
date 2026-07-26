<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\ResellerKlant;
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
     * @return Collection<int, ResellerKlant>
     */
    public function trashed(): Collection;

    public function findTrashedById(int $id): ?ResellerKlant;
}
