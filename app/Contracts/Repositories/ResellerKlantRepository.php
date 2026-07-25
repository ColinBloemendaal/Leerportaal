<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\ResellerKlant;
use Illuminate\Pagination\LengthAwarePaginator;

interface ResellerKlantRepository
{
    /**
     * @return LengthAwarePaginator<int, ResellerKlant>
     */
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator;
}
