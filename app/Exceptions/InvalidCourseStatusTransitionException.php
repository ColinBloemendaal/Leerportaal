<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\CourseStatus;
use DomainException;

final class InvalidCourseStatusTransitionException extends DomainException
{
    public static function from(CourseStatus $from, CourseStatus $to): self
    {
        return new self("Cannot move a course from {$from->value} to {$to->value}.");
    }
}
