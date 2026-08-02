<?php

declare(strict_types=1);

namespace App\Services\Progress;

use App\Models\Course;
use App\Models\CourseAssignment;
use Illuminate\Support\Carbon;

/**
 * Deadline/overdue calculation and reminder-schedule lookup only --
 * deliberately does not send anything or track which reminders have
 * already gone out. The mailable, its dispatch, and its own
 * sent-tracking are Phase 8's job (Notifications, billing, GDPR), not
 * enrollment data modeling.
 */
final readonly class AssignmentDeadlineService
{
    public function __construct(private CourseCompletionChecker $completionChecker) {}

    public function isOverdue(CourseAssignment $assignment, Course $course): bool
    {
        if ($assignment->deadline_at === null || $assignment->revoked_at !== null) {
            return false;
        }

        if (! $assignment->deadline_at->isPast()) {
            return false;
        }

        return ! $this->completionChecker->isComplete($assignment, $course);
    }

    /**
     * Which of the assignment's configured reminder offsets (days
     * before the deadline) fall exactly on the given date. A caller
     * (Phase 8's scheduled job) runs this daily and decides for itself
     * whether each returned offset still needs sending.
     *
     * @return list<int>
     */
    public function reminderOffsetsDueOn(CourseAssignment $assignment, Carbon $date): array
    {
        if ($assignment->deadline_at === null) {
            return [];
        }

        $deadline = $assignment->deadline_at;

        /** @var list<int> $configuredOffsets */
        $configuredOffsets = $assignment->reminder_days_before ?? [];

        return array_values(collect($configuredOffsets)
            ->filter(fn (int $daysBefore): bool => $deadline->subDays($daysBefore)->isSameDay($date))
            ->all());
    }
}
