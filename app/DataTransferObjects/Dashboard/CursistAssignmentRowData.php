<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Dashboard;

use App\Enums\AssignmentStatus;
use Carbon\CarbonImmutable;

/**
 * One row of the cursist dashboard. Passed directly as an Inertia prop
 * (no Http\Resource wrapper) -- CLAUDE.md §3a names a DTO as the other
 * sanctioned way to shape Inertia props besides Http\Resources, and a
 * plain readonly DTO's public properties already json_encode() the way
 * the frontend needs, with no model attributes to accidentally leak.
 */
final readonly class CursistAssignmentRowData
{
    public function __construct(
        public int $assignmentId,
        public int $courseId,
        public string $courseTitle,
        public AssignmentStatus $status,
        public float $progressPercent,
        public bool $isOverdue,
        public ?CarbonImmutable $deadlineAt,
    ) {}
}
