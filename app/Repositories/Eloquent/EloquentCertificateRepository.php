<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CertificateRepository;
use App\Models\Certificate;

final class EloquentCertificateRepository implements CertificateRepository
{
    public function findByVerificationCode(string $code): ?Certificate
    {
        return Certificate::query()
            ->where('verification_code', $code)
            // withoutTenantScope(): the public verification page has no
            // reseller context at all -- a visitor checking a
            // certificate isn't logged into any tenant, and shouldn't
            // need to be. The random verification_code is what
            // authorizes this lookup, not tenant membership, same
            // reasoning as ResellerThemeRepository::findForReseller()
            // for queue workers.
            ->with(['courseAssignment' => fn ($query) => $query->withoutTenantScope()->with(['user', 'course'])])
            ->first();
    }
}
