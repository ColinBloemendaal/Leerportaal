<?php

declare(strict_types=1);

namespace App\Services\Access;

use App\Contracts\Repositories\CourseAccessGrantRepository;
use App\DataTransferObjects\Access\CourseAccessExplanationData;
use App\Enums\CourseAccessReason;
use App\Models\Course;
use App\Models\CourseAccessGrant;
use App\Models\Reseller;

/**
 * Platform -> reseller access for catalog (platform-owned) courses.
 * Reseller-authored courses need no grant -- a reseller always has
 * access to its own courses.
 *
 * Category grants are matched against the course's *current*
 * course_category_id, read fresh on every call rather than snapshotted
 * anywhere, so a course added to an already-granted category becomes
 * accessible immediately without a new grant.
 */
final readonly class CourseAccessChecker
{
    public function __construct(private CourseAccessGrantRepository $grants) {}

    public function resellerHasAccess(Reseller $reseller, Course $course): bool
    {
        return $this->explain($reseller, $course)->hasAccess;
    }

    public function explain(Reseller $reseller, Course $course): CourseAccessExplanationData
    {
        if ($course->reseller_id === $reseller->id) {
            return new CourseAccessExplanationData(true, CourseAccessReason::OwnedByReseller);
        }

        if ($course->reseller_id !== null) {
            // Owned by a different reseller entirely -- reseller-authored
            // courses are never shared via grants, only catalog courses.
            return new CourseAccessExplanationData(false, CourseAccessReason::NoAccess);
        }

        $activeGrants = $this->grants->activeGrantsForReseller($reseller->id);

        $directGrant = $activeGrants->first(
            fn (CourseAccessGrant $grant): bool => $grant->course_id === $course->id,
        );

        if ($directGrant !== null) {
            return new CourseAccessExplanationData(true, CourseAccessReason::DirectGrant, $directGrant->id);
        }

        $categoryGrant = $course->course_category_id === null
            ? null
            : $activeGrants->first(
                fn (CourseAccessGrant $grant): bool => $grant->course_category_id === $course->course_category_id,
            );

        if ($categoryGrant !== null) {
            return new CourseAccessExplanationData(true, CourseAccessReason::CategoryGrant, $categoryGrant->id);
        }

        return new CourseAccessExplanationData(false, CourseAccessReason::NoAccess);
    }
}
