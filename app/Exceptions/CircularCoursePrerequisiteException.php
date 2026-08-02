<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class CircularCoursePrerequisiteException extends DomainException
{
    public static function between(int $courseId, int $prerequisiteId): self
    {
        return new self(
            "Cannot add course #{$prerequisiteId} as a prerequisite of course #{$courseId}: ".
            'it would create a circular dependency.',
        );
    }
}
