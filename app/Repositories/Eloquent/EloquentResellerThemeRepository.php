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
}
