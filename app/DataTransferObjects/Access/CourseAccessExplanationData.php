<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Access;

use App\Enums\CourseAccessReason;

/**
 * Backend calculation only, for the "why can this user see this course"
 * debug view -- no consuming admin page yet, per this codebase's
 * established pattern of deferring admin-gated UI until the Phase 7
 * admin panel exists.
 */
final readonly class CourseAccessExplanationData
{
    public function __construct(
        public bool $hasAccess,
        public CourseAccessReason $reason,
        public ?int $grantId = null,
    ) {}
}
