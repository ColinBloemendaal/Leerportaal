<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepository
{
    /**
     * Every platform (catalog) course plus the current reseller's own --
     * never another reseller's. Course has no TenantScope, same reasoning
     * as CourseCategory -- see App\Models\Course.
     *
     * @return Collection<int, Course>
     */
    public function visibleToCurrentReseller(): Collection;
}
