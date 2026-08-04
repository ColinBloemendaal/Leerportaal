<?php

declare(strict_types=1);

namespace App\Exceptions;

use Carbon\CarbonInterface;
use DomainException;

final class QuizAttemptNotAllowedException extends DomainException
{
    public static function attemptLimitReached(int $limit): self
    {
        return new self("This quiz allows at most {$limit} attempt(s); that limit has already been reached.");
    }

    public static function alreadySubmitted(): self
    {
        return new self('This attempt has already been submitted and cannot be submitted again.');
    }

    public static function cooldownActive(CarbonInterface $availableAt): self
    {
        return new self("Another attempt isn't allowed yet -- available again at {$availableAt->toIso8601String()}.");
    }
}
