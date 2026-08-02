<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Courses;

/**
 * Deliberately does not carry a courseId -- unlike CLAUDE.md §3a's
 * illustrative sketch, AssignCourseToCursist takes the Course model
 * directly (same "no-DTO-for-an-existing-entity" pattern as the other
 * state-transition Actions in App\Actions\Courses). A courseId field
 * here would need a CourseRepository::find() to resolve it, and
 * CLAUDE.md §3a itself bans pass-through repository methods that add
 * nothing over Eloquent -- the more specific rule wins.
 */
final readonly class AssignCourseData
{
    public function __construct(
        public int $userId,
        public int $assignedByUserId,
    ) {}
}
