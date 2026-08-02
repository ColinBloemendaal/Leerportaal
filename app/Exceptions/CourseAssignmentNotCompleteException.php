<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class CourseAssignmentNotCompleteException extends DomainException
{
    public static function for(int $courseAssignmentId): self
    {
        return new self("Cannot issue a certificate for course assignment #{$courseAssignmentId}: it is not complete yet.");
    }
}
