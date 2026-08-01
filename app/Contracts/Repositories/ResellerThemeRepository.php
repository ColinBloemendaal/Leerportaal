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

    /**
     * For code paths that already know exactly which reseller they need
     * (e.g. a Mailable branding an email for a specific reseller) and
     * cannot rely on ambient TenantContext -- queued jobs run in a
     * worker process with no request, so it's never populated there.
     */
    public function findForReseller(int $resellerId): ?ResellerTheme;
}
