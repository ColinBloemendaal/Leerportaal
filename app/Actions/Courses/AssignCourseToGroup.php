<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Group;
use Illuminate\Support\Collection;

final readonly class AssignCourseToGroup
{
    public function __construct(private BulkAssignCourseToCursists $bulkAssign) {}

    /**
     * @return Collection<int, CourseAssignment>
     */
    public function __invoke(Course $course, Group $group, int $assignedByUserId): Collection
    {
        /** @var list<int> $memberIds */
        $memberIds = $group->members()->pluck('users.id')->all();

        return ($this->bulkAssign)($course, $memberIds, $assignedByUserId);
    }
}
