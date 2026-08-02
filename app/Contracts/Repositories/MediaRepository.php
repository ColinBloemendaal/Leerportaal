<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;

interface MediaRepository
{
    /**
     * Every platform media item plus the current reseller's own -- never
     * another reseller's. Media has no TenantScope, same reasoning as
     * Course/CourseCategory -- see App\Models\Media.
     *
     * @return Collection<int, Media>
     */
    public function visibleToCurrentReseller(): Collection;
}
