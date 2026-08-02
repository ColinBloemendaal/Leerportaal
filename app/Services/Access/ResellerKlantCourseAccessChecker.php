<?php

declare(strict_types=1);

namespace App\Services\Access;

use App\Contracts\Repositories\ResellerKlantCourseGrantRepository;
use App\DataTransferObjects\Access\ResellerKlantCourseAccessExplanationData;
use App\Enums\ResellerKlantCourseAccessReason;
use App\Models\Course;
use App\Models\ResellerKlant;
use App\Models\ResellerKlantCourseGrant;
use LogicException;

/**
 * Layered on top of CourseAccessChecker: a resellerklant may only be
 * granted (and therefore may only assign) a course the reseller itself
 * already has access to. The reseller-level check always runs first --
 * a resellerklant-level grant can never widen access beyond what its
 * reseller has.
 */
final readonly class ResellerKlantCourseAccessChecker
{
    public function __construct(
        private CourseAccessChecker $resellerAccess,
        private ResellerKlantCourseGrantRepository $grants,
    ) {}

    public function canAssign(ResellerKlant $klant, Course $course): bool
    {
        return $this->explain($klant, $course)->hasAccess;
    }

    public function explain(ResellerKlant $klant, Course $course): ResellerKlantCourseAccessExplanationData
    {
        // BelongsTo::reseller() is typed nullable by Larastan regardless
        // of the FK being required -- see GradingService for the same
        // pattern.
        $reseller = $klant->reseller;

        if ($reseller === null) {
            throw new LogicException("ResellerKlant #{$klant->id} has no Reseller.");
        }

        if (! $this->resellerAccess->resellerHasAccess($reseller, $course)) {
            return new ResellerKlantCourseAccessExplanationData(false, ResellerKlantCourseAccessReason::ResellerLacksAccess);
        }

        $grant = $this->grants->activeGrantsForResellerKlant($klant->id)
            ->first(fn (ResellerKlantCourseGrant $grant): bool => $grant->course_id === $course->id);

        if ($grant !== null) {
            return new ResellerKlantCourseAccessExplanationData(true, ResellerKlantCourseAccessReason::GrantedToKlant, $grant->id);
        }

        return new ResellerKlantCourseAccessExplanationData(false, ResellerKlantCourseAccessReason::NotGrantedToKlant);
    }
}
