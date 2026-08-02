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

    /**
     * Sum of size_bytes for that reseller's own media only (never
     * platform media -- platform storage isn't billed to any reseller).
     * Explicitly parameterized, not ambient-tenant-scoped, so it can be
     * used for any reseller (e.g. platform admin reporting), not just
     * the current one.
     */
    public function totalBytesForReseller(int $resellerId): int;

    /**
     * Sum of size_bytes across every media row, platform and reseller
     * owned alike -- for the platform admin dashboard's storage figure.
     */
    public function totalBytesAcrossPlatform(): int;
}
