<?php

declare(strict_types=1);

namespace App\Actions\Access;

use App\Models\CourseAccessGrant;

/**
 * Sets revoked_at rather than deleting -- keeps the audit trail, and per
 * this phase's own requirement, revoking access never cascades into
 * deleting progress/assignments/certificates (this table has no
 * relationship to any of them, so there is nothing to cascade).
 */
final readonly class RevokeCourseAccess
{
    public function __invoke(CourseAccessGrant $grant): CourseAccessGrant
    {
        $grant->update(['revoked_at' => now()]);

        return $grant;
    }
}
