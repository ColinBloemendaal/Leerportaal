<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CourseAssignmentRepository;
use App\Models\CourseAssignment;
use Illuminate\Database\Eloquent\Collection;

final class EloquentCourseAssignmentRepository implements CourseAssignmentRepository
{
    public function forUser(int $userId): Collection
    {
        return CourseAssignment::query()
            ->where('user_id', $userId)
            ->whereNull('revoked_at')
            ->with('course')
            ->orderByDesc('assigned_at')
            ->get();
    }
}
