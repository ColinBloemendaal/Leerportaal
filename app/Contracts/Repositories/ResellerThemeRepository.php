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

    /**
     * For the theme editor: a fresh, unsaved instance with the schema's
     * own defaults when the current reseller has no theme row yet, so
     * the edit form always has something to pre-fill from.
     */
    public function findForCurrentResellerOrDefault(): ResellerTheme;
}
