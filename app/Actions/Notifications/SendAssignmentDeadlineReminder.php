<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Contracts\Repositories\AssignmentReminderRepository;
use App\Enums\NotificationType;
use App\Models\AssignmentReminder;
use App\Models\CourseAssignment;
use App\Notifications\AssignmentDeadlineNotification;
use App\Notifications\AssignmentOverdueNotification;
use App\Services\Progress\AssignmentDeadlineService;
use App\Services\Progress\CourseCompletionChecker;
use App\Tenancy\TenantContext;
use Illuminate\Support\Carbon;

/**
 * One assignment per call, evaluated against "today" -- the daily
 * scheduled command (SendAssignmentDeadlineRemindersCommand) is what
 * iterates every assignment platform-wide and calls this once each,
 * same split as SendKlantProgressReport/SendKlantProgressReportsCommand.
 *
 * Deliberately does not touch AssignmentDeadlineService (Phase 5) itself
 * -- that service is pure calculation by design; this Action owns the
 * send-once-per-offset bookkeeping its own docblock says is Phase 8's job.
 */
final readonly class SendAssignmentDeadlineReminder
{
    public function __construct(
        private AssignmentDeadlineService $deadlineService,
        private CourseCompletionChecker $completionChecker,
        private AssignmentReminderRepository $reminders,
        private TenantContext $tenantContext,
    ) {}

    /**
     * Returns the number of notifications sent for this assignment (0, 1,
     * or -- rarely, if multiple configured offsets land on the same day
     * after a gap in the schedule running -- more than 1).
     */
    public function __invoke(CourseAssignment $assignment, Carbon $today): int
    {
        $course = $assignment->course;
        $user = $assignment->user;
        $reseller = $assignment->reseller;

        if ($course === null || $user === null || $reseller === null) {
            return 0;
        }

        if ($this->completionChecker->isComplete($assignment, $course)) {
            return 0;
        }

        // Needed for hasBeenSent()'s TenantScoped read and the reminder
        // row's own scope on write -- this Action is called from a
        // platform-wide command with no single ambient tenant.
        $this->tenantContext->set($reseller);

        if ($this->deadlineService->isOverdue($assignment, $course)) {
            if ($this->reminders->hasBeenSent($assignment->id, NotificationType::Overdue, null)) {
                return 0;
            }

            $user->notify(new AssignmentOverdueNotification($assignment));
            $this->recordSent($assignment, NotificationType::Overdue, null);

            return 1;
        }

        $sent = 0;

        foreach ($this->deadlineService->reminderOffsetsDueOn($assignment, $today) as $daysBefore) {
            if ($this->reminders->hasBeenSent($assignment->id, NotificationType::Deadline, $daysBefore)) {
                continue;
            }

            $user->notify(new AssignmentDeadlineNotification($assignment, $daysBefore));
            $this->recordSent($assignment, NotificationType::Deadline, $daysBefore);
            $sent++;
        }

        return $sent;
    }

    private function recordSent(CourseAssignment $assignment, NotificationType $type, ?int $daysBefore): void
    {
        AssignmentReminder::query()->create([
            'reseller_id' => $assignment->reseller_id,
            'course_assignment_id' => $assignment->id,
            'type' => $type,
            'days_before' => $daysBefore,
            'sent_at' => now(),
        ]);
    }
}
