<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\ResellerTheme;

interface ResellerThemeRepository
{
    /**
     * Null when no tenant is resolved, or the current reseller has no
     * theme row yet -- both mean "render the unbranded defaults."
     */
    public function findForCurrentReseller(): ?ResellerTheme;
}
