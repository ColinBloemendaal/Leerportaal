<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Contracts\Repositories\ResellerRepository;
use App\Models\Reseller;
use Illuminate\Http\Request;

/**
 * Resolution chain per CLAUDE.md §1: custom domain -> tenant cookie ->
 * fallback. Bare browsing with neither is the legitimate unbranded
 * experience, not an error.
 *
 * Custom domain resolution lands here once the custom domain table and
 * verification flow exist (Phase 1 task 22) -- for now, cookie only.
 */
final class TenantResolver
{
    public function __construct(private readonly ResellerRepository $resellers) {}

    public function resolve(Request $request): ?Reseller
    {
        return $this->resolveByCookie($request);
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
