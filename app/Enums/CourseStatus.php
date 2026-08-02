<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The states a course can be in. Transition rules (who can move a course
 * from one to the next, versioning on publish) are Phase 3's "Draft ->
 * review -> published workflow with versioning" task -- this enum just
 * names the states the `courses` table needs today.
 */
enum CourseStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InReview => 'In review',
            self::Published => 'Published',
        };
    }
}
