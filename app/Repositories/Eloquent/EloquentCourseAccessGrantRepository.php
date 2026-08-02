<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CourseAccessGrantRepository;
use App\Models\CourseAccessGrant;
use Illuminate\Database\Eloquent\Collection;

final class EloquentCourseAccessGrantRepository implements CourseAccessGrantRepository
{
    public function activeGrantsForReseller(int $resellerId): Collection
    {
        return CourseAccessGrant::query()
            // withoutTenantScope(): the reseller being checked is not
            // necessarily the ambient tenant -- a platform admin explaining
            // access for reseller X has no reseller context of their own,
            // same reasoning as EloquentCertificateRepository. $resellerId
            // is filtered explicitly below instead.
            ->withoutTenantScope()
            ->where('reseller_id', $resellerId)
            ->whereNull('revoked_at')
            ->get();
    }
}
