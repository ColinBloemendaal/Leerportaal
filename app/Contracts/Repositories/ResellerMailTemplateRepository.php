<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\MailTemplateType;
use App\Models\ResellerMailTemplate;

interface ResellerMailTemplateRepository
{
    /**
     * Null means "no override -- render the default." Used by the editor,
     * within a request, so ambient TenantContext is reliable.
     */
    public function findForCurrentReseller(MailTemplateType $type): ?ResellerMailTemplate;

    /**
     * For code paths that already know exactly which reseller they need
     * and cannot rely on ambient TenantContext -- see
     * ResellerThemeRepository::findForReseller for why (queued Mailables
     * run in a worker process with no request).
     */
    public function findForResellerAndType(int $resellerId, MailTemplateType $type): ?ResellerMailTemplate;
}
