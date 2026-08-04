<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Courses;

/**
 * No courseId/groupId, same reasoning as AssignCourseData -- AssignCourseToGroup
 * takes the Course and Group models directly.
 */
final readonly class GroupAssignCourseData
{
    public function __construct(
        public int $assignedByUserId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            assignedByUserId: (int) $data['assigned_by_user_id'],
        );
    }
}
