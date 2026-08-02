<?php

declare(strict_types=1);

namespace App\Actions\Access;

use App\Models\ResellerKlantCourseGrant;

/**
 * Sets revoked_at rather than deleting, same reasoning as
 * RevokeCourseAccess -- keeps the audit trail, never cascades into
 * deleting progress/assignments/certificates.
 */
final readonly class RevokeCourseFromResellerKlant
{
    public function __invoke(ResellerKlantCourseGrant $grant): ResellerKlantCourseGrant
    {
        $grant->update(['revoked_at' => now()]);

        return $grant;
    }
}
