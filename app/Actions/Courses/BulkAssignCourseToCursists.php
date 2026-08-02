<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\DataTransferObjects\Courses\AssignCourseData;
use App\Models\Course;
use App\Models\CourseAssignment;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

final readonly class BulkAssignCourseToCursists
{
    public function __construct(
        private AssignCourseToCursist $assignCourseToCursist,
        private ConnectionInterface $db,
    ) {}

    /**
     * @param  list<int>  $userIds
     * @return Collection<int, CourseAssignment>
     */
    public function __invoke(Course $course, array $userIds, int $assignedByUserId): Collection
    {
        return $this->db->transaction(
            fn (): Collection => collect($userIds)->map(
                fn (int $userId): CourseAssignment => ($this->assignCourseToCursist)(
                    $course,
                    new AssignCourseData($userId, $assignedByUserId),
                ),
            ),
        );
    }
}
