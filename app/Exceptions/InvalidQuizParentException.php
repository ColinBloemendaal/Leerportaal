<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class InvalidQuizParentException extends DomainException
{
    public static function mustHaveExactlyOneParent(): self
    {
        return new self('A quiz must belong to exactly one of a module or a lesson, not zero or both.');
    }
}
