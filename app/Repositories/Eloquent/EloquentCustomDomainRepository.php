<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CustomDomainRepository;
use App\Enums\CustomDomainStatus;
use App\Models\CustomDomain;

final class EloquentCustomDomainRepository implements CustomDomainRepository
{
    public function findVerifiedByDomain(string $domain): ?CustomDomain
    {
        // Platform context: resolving which tenant a domain belongs to
        // must work before any tenant is known -- that's the whole point
        // of this lookup, so it can't be scoped to one.
        return CustomDomain::query()
            ->withoutTenantScope()
            ->with('reseller')
            ->where('domain', $domain)
            ->where('status', CustomDomainStatus::Verified)
            ->first();
    }
}
