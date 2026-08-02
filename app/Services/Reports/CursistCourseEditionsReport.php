<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\DataTransferObjects\Reports\CursistCourseEditionData;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\User;
use App\Services\Progress\CourseCompletionChecker;
use Illuminate\Support\Collection;

/**
 * "Which editions has this cursist done" -- one row per course variant
 * in a repeat lineage (root + every variants() entry, oldest to newest),
 * regardless of whether this particular cursist was ever assigned that
 * specific edition.
 */
final readonly class CursistCourseEditionsReport
{
    public function __construct(private CourseCompletionChecker $completionChecker) {}

    /**
     * @return Collection<int, CursistCourseEditionData>
     */
    public function forCursist(User $user, Course $anyEditionOfCourse): Collection
    {
        return $this->allEditionsOf($anyEditionOfCourse)->map(function (Course $edition) use ($user): CursistCourseEditionData {
            $assignment = CourseAssignment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $edition->id)
                ->latest('assigned_at')
                ->first();

            return new CursistCourseEditionData(
                courseId: $edition->id,
                variantYear: $edition->variant_year,
                wasAssigned: $assignment !== null,
                assignedAt: $assignment?->assigned_at,
                isComplete: $assignment !== null && $this->completionChecker->isComplete($assignment, $edition),
            );
        });
    }

    /**
     * @return Collection<int, Course>
     */
    private function allEditionsOf(Course $course): Collection
    {
        $root = $course;

        while ($root->repeats_from_course_id !== null) {
            $parent = $root->repeatsFrom;

            if ($parent === null) {
                break;
            }

            $root = $parent;
        }

        return collect([$root])->merge($root->variants()->orderBy('variant_year')->get());
    }
}
