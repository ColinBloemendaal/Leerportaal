<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\Models\Course;

final readonly class RemoveCoursePrerequisite
{
    public function __invoke(Course $course, Course $prerequisite): void
    {
        $course->prerequisites()->detach($prerequisite->id);
    }
}
