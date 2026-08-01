<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerMailTemplateRepository;
use App\Enums\MailTemplateType;
use App\Models\ResellerMailTemplate;

final class EloquentResellerMailTemplateRepository implements ResellerMailTemplateRepository
{
    public function findForCurrentReseller(MailTemplateType $type): ?ResellerMailTemplate
    {
        // TenantScope already fails closed (no tenant -> no rows), so this
        // is a normal scoped read, not a bypass.
        return ResellerMailTemplate::query()->where('type', $type)->first();
    }

    public function findForResellerAndType(int $resellerId, MailTemplateType $type): ?ResellerMailTemplate
    {
        // Explicitly parameterized by the caller's own reseller ID -- see
        // EloquentResellerThemeRepository::findForReseller for why this
        // bypass doesn't weaken isolation.
        return ResellerMailTemplate::query()
            ->withoutTenantScope()
            ->where('reseller_id', $resellerId)
            ->where('type', $type)
            ->first();
    }
}
