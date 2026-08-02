<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Tracks whether a course_assignment (the billable event per CLAUDE.md
 * §11) has actually been invoiced yet -- the pricing calculation itself
 * and real invoice generation are a later Phase 8 billing task; this is
 * just the state a course_assignment sits in until that exists.
 */
enum AssignmentBillingState: string
{
    case Pending = 'pending';
    case Billed = 'billed';
    case Waived = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Billed => 'Billed',
            self::Waived => 'Waived',
        };
    }
}
