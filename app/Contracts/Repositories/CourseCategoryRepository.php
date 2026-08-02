<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Collection;

interface CourseCategoryRepository
{
    /**
     * Every platform category (reseller_id null) plus the current
     * reseller's own -- never another reseller's. CourseCategory has no
     * TenantScope (a row can be platform- or reseller-owned, a shape the
     * strict global scope doesn't support), so visibility is composed
     * here explicitly instead.
     *
     * @return Collection<int, CourseCategory>
     */
    public function visibleToCurrentReseller(): Collection;
}
