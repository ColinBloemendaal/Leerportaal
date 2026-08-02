<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\ResellerKlantCourseGrant;
use Illuminate\Database\Eloquent\Collection;

interface ResellerKlantCourseGrantRepository
{
    /**
     * Explicitly parameterized by resellerklant id rather than relying on
     * ambient TenantContext, same reasoning as
     * CourseAccessGrantRepository::activeGrantsForReseller().
     *
     * @return Collection<int, ResellerKlantCourseGrant>
     */
    public function activeGrantsForResellerKlant(int $resellerKlantId): Collection;
}
