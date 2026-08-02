<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Access;

final readonly class GrantCourseToResellerKlantData
{
    public function __construct(
        public int $resellerKlantId,
        public int $courseId,
        public ?int $grantedByUserId = null,
    ) {}
}
