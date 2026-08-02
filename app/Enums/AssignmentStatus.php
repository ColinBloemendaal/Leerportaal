<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Derived status for the cursist dashboard -- not stored on
 * CourseAssignment itself, computed from first_opened_at + completion.
 */
enum AssignmentStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not started',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
        };
    }
}
