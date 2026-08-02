<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Course;
use DomainException;

final class CircularCoursePrerequisiteException extends DomainException
{
    public static function between(Course $course, Course $prerequisite): self
    {
        return new self(
            "Cannot add course #{$prerequisite->id} as a prerequisite of course #{$course->id}: ".
            'it would create a circular dependency.',
        );
    }
}
