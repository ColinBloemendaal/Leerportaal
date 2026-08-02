<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class InvalidCourseAccessGrantException extends DomainException
{
    public static function mustHaveExactlyOneTarget(): self
    {
        return new self('A course access grant must target exactly one of a course or a course category, not zero or both.');
    }
}
