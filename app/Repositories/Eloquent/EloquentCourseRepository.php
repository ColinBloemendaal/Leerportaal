<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CourseRepository;
use App\Models\Course;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;

final class EloquentCourseRepository implements CourseRepository
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function visibleToCurrentReseller(): Collection
    {
        $tenantId = $this->tenantContext->id();

        return Course::query()
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('reseller_id')->orWhere('reseller_id', $tenantId);
            })
            ->get();
    }
}
