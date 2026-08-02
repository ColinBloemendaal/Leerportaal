<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Access;

/**
 * Exactly one of courseId/courseCategoryId must be set -- mirrors the
 * exactly-one-parent rule enforced on CourseAccessGrant itself.
 */
final readonly class GrantCourseAccessData
{
    public function __construct(
        public int $resellerId,
        public ?int $courseId,
        public ?int $courseCategoryId,
        public ?int $grantedByUserId = null,
    ) {}
}
