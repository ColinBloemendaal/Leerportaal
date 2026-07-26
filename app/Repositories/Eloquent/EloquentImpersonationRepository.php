<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ImpersonationRepository;
use App\Models\Impersonation;

final class EloquentImpersonationRepository implements ImpersonationRepository
{
    public function findActive(int $id): ?Impersonation
    {
        return Impersonation::query()->whereKey($id)->whereNull('ended_at')->first();
    }
}
