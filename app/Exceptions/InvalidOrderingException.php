<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class InvalidOrderingException extends DomainException
{
    public static function mismatchedIds(): self
    {
        return new self('The given ordered IDs do not exactly match the siblings being reordered.');
    }
}
