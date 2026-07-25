<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Scopes\TenantScope;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait TenantScoped
{
    protected static function bootTenantScoped(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $model): void {
            // getAttribute()/setAttribute(), not the magic property: the
            // column is NOT NULL, but the in-memory value genuinely can be
            // unset here, before the INSERT happens.
            if ($model->getAttribute('reseller_id') === null) {
                $tenantId = app(TenantContext::class)->id();

                if ($tenantId !== null) {
                    $model->setAttribute('reseller_id', $tenantId);
                }
            }
        });
    }

    /**
     * Escape hatch for admin/platform contexts. Every call site must carry
     * a comment explaining why -- see CLAUDE.md §2.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
