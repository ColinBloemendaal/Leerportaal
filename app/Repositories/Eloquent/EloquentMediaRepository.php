<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\MediaRepository;
use App\Models\Media;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;

final class EloquentMediaRepository implements MediaRepository
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function visibleToCurrentReseller(): Collection
    {
        $tenantId = $this->tenantContext->id();

        return Media::query()
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('reseller_id')->orWhere('reseller_id', $tenantId);
            })
            ->get();
    }

    public function totalBytesForReseller(int $resellerId): int
    {
        return (int) Media::query()->where('reseller_id', $resellerId)->sum('size_bytes');
    }
}
