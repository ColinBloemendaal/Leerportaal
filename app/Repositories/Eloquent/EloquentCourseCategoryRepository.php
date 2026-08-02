<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CourseCategoryRepository;
use App\Models\CourseCategory;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;

final class EloquentCourseCategoryRepository implements CourseCategoryRepository
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function visibleToCurrentReseller(): Collection
    {
        $tenantId = $this->tenantContext->id();

        return CourseCategory::query()
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('reseller_id')->orWhere('reseller_id', $tenantId);
            })
            ->orderBy('order')
            ->get();
    }
}
