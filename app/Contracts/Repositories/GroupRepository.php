<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;

/**
 * Group is TenantScoped (single-owner, via its resellerklant -- see
 * App\Models\Group), so every method here is implicitly scoped to the
 * current ambient reseller by the global scope, same as
 * CourseAssignmentRepository's own tenant-scoped methods.
 */
interface GroupRepository
{
    /**
     * @return Collection<int, Group>
     */
    public function forCurrentReseller(): Collection;

    public function findForCurrentReseller(int $id): ?Group;
}
