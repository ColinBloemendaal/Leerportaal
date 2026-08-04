<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CertificateRepository;
use App\Models\Certificate;
use App\Models\CourseAssignment;
use Illuminate\Database\Eloquent\Collection;

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

    public function forUser(int $userId): Collection
    {
        // withoutTenantScope(): a user's own certificates must be found via
        // their own assignments regardless of the request's ambient tenant,
        // same reasoning as findByVerificationCode() above. Resolving the
        // assignment ids first (rather than a whereHas() closure) sidesteps
        // needing withoutTenantScope() inside a relation-closure, where its
        // generic type can't be inferred.
        $assignmentIds = CourseAssignment::query()
            ->withoutTenantScope()
            ->where('user_id', $userId)
            ->pluck('id');

        return Certificate::query()
            ->whereIn('course_assignment_id', $assignmentIds)
            ->with(['courseAssignment' => fn ($query) => $query->withoutTenantScope()->with('course')])
            ->get();
    }
}
