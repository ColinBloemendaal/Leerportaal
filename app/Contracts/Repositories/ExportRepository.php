<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Export;
use Illuminate\Database\Eloquent\Collection;

interface ExportRepository
{
    public function findById(int $id): ?Export;

    /**
     * For the download route -- ownership is the only authorization an
     * export needs (see App\Models\Export's own docblock), so this is
     * the one lookup that matters there.
     */
    public function findOwnById(int $userId, int $id): ?Export;

    /**
     * For the "my exports" list -- newest first.
     *
     * @return Collection<int, Export>
     */
    public function forUser(int $userId): Collection;
}
