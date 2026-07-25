<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Contracts\Repositories\CustomDomainRepository;
use App\Contracts\Repositories\ResellerRepository;
use App\Models\Reseller;
use Illuminate\Http\Request;

/**
 * Resolution chain per CLAUDE.md §1: custom domain -> tenant cookie ->
 * fallback. Bare browsing with neither is the legitimate unbranded
 * experience, not an error.
 */
final class TenantResolver
{
    public function __construct(
        private readonly ResellerRepository $resellers,
        private readonly CustomDomainRepository $customDomains,
    ) {}

    public function resolve(Request $request): ?Reseller
    {
        return $this->resolveByCustomDomain($request) ?? $this->resolveByCookie($request);
    }

    private function resolveByCustomDomain(Request $request): ?Reseller
    {
        return $this->customDomains->findVerifiedByDomain($request->getHost())?->reseller;
    }

    private function resolveByCookie(Request $request): ?Reseller
    {
        $slug = $request->cookie('reseller_slug');

        if (! is_string($slug)) {
            return null;
        }

        return $this->resellers->findActiveBySlug($slug);
    }
}
