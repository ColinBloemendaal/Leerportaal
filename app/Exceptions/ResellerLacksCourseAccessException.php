<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class ResellerLacksCourseAccessException extends DomainException
{
    public static function forReseller(int $resellerId, int $courseId): self
    {
        return new self("Reseller {$resellerId} cannot grant course {$courseId} to a resellerklant because the reseller itself has no access to it.");
    }
}
