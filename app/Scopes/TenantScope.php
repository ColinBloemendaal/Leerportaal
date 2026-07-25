<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Fails closed: with no tenant resolved, tenant-scoped rows return
 * nothing rather than everything. See CLAUDE.md §2 -- the only
 * sanctioned escape is ->withoutTenantScope(), in an admin/platform
 * context, with a comment explaining why.
 */
final class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = app(TenantContext::class)->id();

        if ($tenantId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('reseller_id'), $tenantId);
    }
}
