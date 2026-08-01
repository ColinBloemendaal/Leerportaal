<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerThemeRepository;
use App\Models\ResellerTheme;

final class EloquentResellerThemeRepository implements ResellerThemeRepository
{
    public function findForCurrentReseller(): ?ResellerTheme
    {
        // TenantScope already fails closed (no tenant -> no rows), so this
        // is a normal scoped read, not a bypass.
        return ResellerTheme::query()->first();
    }

    public function findForCurrentResellerOrDefault(): ResellerTheme
    {
        return $this->findForCurrentReseller() ?? new ResellerTheme([
            // Must match the reseller_themes migration's column default.
            'primary_color' => '#0d6efd',
        ]);
    }

    public function findForReseller(int $resellerId): ?ResellerTheme
    {
        // Explicitly parameterized by the caller's own reseller ID, so
        // bypassing ambient TenantContext here doesn't weaken isolation --
        // it sidesteps a case where that context is simply never set
        // (queue workers), not a case where the caller doesn't know which
        // tenant it wants.
        return ResellerTheme::query()->withoutTenantScope()->where('reseller_id', $resellerId)->first();
    }
}
