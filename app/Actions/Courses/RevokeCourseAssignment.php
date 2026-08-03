<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\Actions\Billing\WaiveAssignmentBilling;
use App\Models\CourseAssignment;
use Illuminate\Database\ConnectionInterface;

/**
 * CLAUDE.md §11: "Revocation: free within 14 days if the cursist has
 * never opened the course. After first open, or after 14 days, it is
 * billed." Both the timestamp and the free-or-billed outcome are
 * evaluated and stored here, once, at the moment of revocation -- neither
 * is ever recomputed later, so a subsequent report can't retroactively
 * change based on today's date.
 */
final readonly class RevokeCourseAssignment
{
    private const FREE_REVOCATION_DAYS = 14;

    public function __construct(
        private WaiveAssignmentBilling $waiveBilling,
        private ConnectionInterface $db,
    ) {}

    public function __invoke(CourseAssignment $assignment): CourseAssignment
    {
        return $this->db->transaction(function () use ($assignment): CourseAssignment {
            $assignment->revoked_at = now()->toImmutable();
            $assignment->save();

            if ($this->isEligibleForFreeRevocation($assignment)) {
                ($this->waiveBilling)($assignment);
            }

            return $assignment;
        });
    }

    private function isEligibleForFreeRevocation(CourseAssignment $assignment): bool
    {
        if ($assignment->first_opened_at !== null) {
            return false;
        }

        return now()->lessThanOrEqualTo($assignment->assigned_at->addDays(self::FREE_REVOCATION_DAYS));
    }
}
