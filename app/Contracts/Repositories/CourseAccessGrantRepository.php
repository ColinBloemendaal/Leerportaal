<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\CourseAccessGrant;
use Illuminate\Database\Eloquent\Collection;

interface CourseAccessGrantRepository
{
    /**
     * Explicitly parameterized by reseller id rather than relying on the
     * ambient TenantContext, so this works for the reseller's own access
     * checks and for platform-admin cross-tenant "why can this user see
     * this course" lookups alike.
     *
     * @return Collection<int, CourseAccessGrant>
     */
    public function activeGrantsForReseller(int $resellerId): Collection;
}
