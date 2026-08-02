<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Exceptions\InvalidCourseStatusTransitionException;
use App\Models\Course;

/**
 * From either InReview (reviewer sends it back) or Published (author
 * pulls it back for edits) -- both collapse to the same "back to draft"
 * transition, so one Action covers both rather than two near-identical
 * ones.
 */
final readonly class RevertCourseToDraft
{
    public function __invoke(Course $course): Course
    {
        if ($course->status === CourseStatus::Draft) {
            throw InvalidCourseStatusTransitionException::from($course->status, CourseStatus::Draft);
        }

        $course->update(['status' => CourseStatus::Draft]);

        return $course;
    }
}
