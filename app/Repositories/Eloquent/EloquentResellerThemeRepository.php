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
}
