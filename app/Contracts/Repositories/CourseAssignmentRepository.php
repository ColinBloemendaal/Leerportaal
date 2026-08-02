<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\CourseAssignment;
use Illuminate\Database\Eloquent\Collection;

interface CourseAssignmentRepository
{
    /**
     * Explicitly parameterized by user id, not ambient Auth::user(), so
     * this works the same whether called from a request or a queued job.
     *
     * @return Collection<int, CourseAssignment>
     */
    public function forUser(int $userId): Collection;
}
