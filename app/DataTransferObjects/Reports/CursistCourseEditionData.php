<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Reports;

use Carbon\CarbonImmutable;

/**
 * One row of the "which editions has this cursist done" report -- one
 * per course variant in a repeat lineage, whether or not this cursist
 * was ever assigned that particular edition.
 */
final readonly class CursistCourseEditionData
{
    public function __construct(
        public int $courseId,
        public ?int $variantYear,
        public bool $wasAssigned,
        public ?CarbonImmutable $assignedAt,
        public bool $isComplete,
    ) {}
}
