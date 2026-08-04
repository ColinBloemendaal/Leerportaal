<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Courses;

/**
 * No courseId, same reasoning as AssignCourseData -- BulkAssignCourseToCursists
 * takes the Course model directly.
 */
final readonly class BulkAssignCourseData
{
    /**
     * @param  list<int>  $userIds
     */
    public function __construct(
        public array $userIds,
        public int $assignedByUserId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<int> $userIds */
        $userIds = $data['user_ids'];

        return new self(
            userIds: $userIds,
            assignedByUserId: (int) $data['assigned_by_user_id'],
        );
    }
}
