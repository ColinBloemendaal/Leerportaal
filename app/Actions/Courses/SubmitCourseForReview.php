<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Exceptions\InvalidCourseStatusTransitionException;
use App\Models\Course;

final readonly class SubmitCourseForReview
{
    public function __invoke(Course $course): Course
    {
        if ($course->status !== CourseStatus::Draft) {
            throw InvalidCourseStatusTransitionException::from($course->status, CourseStatus::InReview);
        }

        $course->update(['status' => CourseStatus::InReview]);

        return $course;
    }
}
