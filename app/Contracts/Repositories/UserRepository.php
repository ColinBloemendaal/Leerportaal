<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends FilterablePaginator<User>
 */
interface UserRepository extends FilterablePaginator
{
    /**
     * For the platform admin users index -- every user across every
     * reseller, plus platform staff. Deliberately not tenant-scoped:
     * this is the one place a platform admin needs to see everyone.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * For the per-user detail page. Deliberately not tenant-scoped, same
     * reasoning as paginate() -- a platform admin can look up any user.
     */
    public function findById(int $id): ?User;

    /**
     * Platform staff with the super-admin role -- the recipients of admin
     * alert notifications (queue/failed-job health, etc).
     *
     * @return Collection<int, User>
     */
    public function superAdmins(): Collection;
}
