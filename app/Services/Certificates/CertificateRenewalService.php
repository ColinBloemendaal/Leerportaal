<?php

declare(strict_types=1);

namespace App\Services\Certificates;

use App\Models\Certificate;
use App\Models\Course;

/**
 * "Drives repetition courses" per CLAUDE.md's own phrasing: this doesn't
 * auto-renew anything (no reassignment per §11 -- a repeat is a fresh,
 * freshly-billed assignment, and deciding to make one is a human/admin
 * action or a later notification-flow trigger, not this service's job).
 * It only answers "is this certificate due for renewal" and "what's the
 * course to renew into."
 */
final readonly class CertificateRenewalService
{
    public function isExpired(Certificate $certificate): bool
    {
        return $certificate->expires_at !== null && $certificate->expires_at->isPast();
    }

    public function isExpiringSoon(Certificate $certificate, int $withinDays = 30): bool
    {
        if ($certificate->expires_at === null || $this->isExpired($certificate)) {
            return false;
        }

        return $certificate->expires_at->lessThanOrEqualTo(now()->addDays($withinDays));
    }

    /**
     * The course to assign for renewal: the newest variant in this
     * course's repeat lineage (by variant_year), regardless of whether
     * $course itself is the original or an older variant. Falls back to
     * $course itself when no variant exists yet.
     */
    public function latestVariantOf(Course $course): Course
    {
        $root = $course;

        while ($root->repeats_from_course_id !== null) {
            $parent = $root->repeatsFrom;

            if ($parent === null) {
                break;
            }

            $root = $parent;
        }

        $latestVariant = $root->variants()->orderByDesc('variant_year')->first();

        return $latestVariant ?? $root;
    }
}
