<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Access;

use App\Enums\ResellerKlantCourseAccessReason;

final readonly class ResellerKlantCourseAccessExplanationData
{
    public function __construct(
        public bool $hasAccess,
        public ResellerKlantCourseAccessReason $reason,
        public ?int $grantId = null,
    ) {}
}
