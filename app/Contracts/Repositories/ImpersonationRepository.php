<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Impersonation;

interface ImpersonationRepository
{
    /**
     * Null if the impersonation doesn't exist or has already ended --
     * callers don't need to distinguish why.
     */
    public function findActive(int $id): ?Impersonation;
}
