<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerKlantCourseGrantRepository;
use App\Models\ResellerKlantCourseGrant;
use Illuminate\Database\Eloquent\Collection;

final class EloquentResellerKlantCourseGrantRepository implements ResellerKlantCourseGrantRepository
{
    public function activeGrantsForResellerKlant(int $resellerKlantId): Collection
    {
        return ResellerKlantCourseGrant::query()
            // withoutTenantScope(): mirrors EloquentCourseAccessGrantRepository
            // -- the resellerklant being checked is not necessarily owned
            // by the ambient tenant (e.g. a platform admin's debug view
            // has no reseller context at all). $resellerKlantId is
            // filtered explicitly below instead.
            ->withoutTenantScope()
            ->where('resellerklant_id', $resellerKlantId)
            ->whereNull('revoked_at')
            ->get();
    }
}
