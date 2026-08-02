<?php

declare(strict_types=1);

namespace App\Services\Progress;

use App\Contracts\Repositories\CourseAssignmentRepository;
use App\DataTransferObjects\Dashboard\CursistAssignmentRowData;
use App\Enums\AssignmentStatus;
use App\Models\CourseAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use LogicException;

/**
 * Assembles the cursist dashboard's rows: assigned/in-progress/completed
 * status plus progress percent and overdue flag, per active (non-revoked)
 * assignment. Combines CourseAssignmentRepository with the progress/
 * completion/deadline services already built earlier in this phase --
 * it doesn't duplicate their calculations.
 */
final readonly class CursistDashboardService
{
    public function __construct(
        private CourseAssignmentRepository $assignments,
        private ProgressCalculationService $progress,
        private CourseCompletionChecker $completionChecker,
        private AssignmentDeadlineService $deadlines,
    ) {}

    /**
     * @return Collection<int, CursistAssignmentRowData>
     */
    public function forUser(User $user): Collection
    {
        return $this->assignments->forUser($user->id)->map(
            fn (CourseAssignment $assignment): CursistAssignmentRowData => $this->rowFor($assignment),
        );
    }

    private function rowFor(CourseAssignment $assignment): CursistAssignmentRowData
    {
        $course = $assignment->course;

        if ($course === null) {
            throw new LogicException("CourseAssignment #{$assignment->id} has no Course.");
        }

        $isComplete = $this->completionChecker->isComplete($assignment, $course);

        $status = match (true) {
            $isComplete => AssignmentStatus::Completed,
            $assignment->first_opened_at !== null => AssignmentStatus::InProgress,
            default => AssignmentStatus::NotStarted,
        };

        return new CursistAssignmentRowData(
            assignmentId: $assignment->id,
            courseId: $course->id,
            courseTitle: $course->title,
            status: $status,
            progressPercent: $this->progress->courseCompletionPercent($assignment, $course),
            isOverdue: $this->deadlines->isOverdue($assignment, $course),
            deadlineAt: $assignment->deadline_at,
        );
    }
}
