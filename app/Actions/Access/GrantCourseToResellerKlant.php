<?php

declare(strict_types=1);

namespace App\Actions\Access;

use App\DataTransferObjects\Access\GrantCourseToResellerKlantData;
use App\Exceptions\ResellerLacksCourseAccessException;
use App\Models\Course;
use App\Models\ResellerKlant;
use App\Models\ResellerKlantCourseGrant;
use App\Services\Access\CourseAccessChecker;
use LogicException;

final readonly class GrantCourseToResellerKlant
{
    public function __construct(private CourseAccessChecker $resellerAccess) {}

    public function __invoke(GrantCourseToResellerKlantData $data): ResellerKlantCourseGrant
    {
        $klant = ResellerKlant::query()->withoutTenantScope()->findOrFail($data->resellerKlantId);
        $course = Course::query()->findOrFail($data->courseId);

        // BelongsTo::reseller() is typed nullable by Larastan regardless
        // of the FK being required -- see GradingService for the same
        // pattern.
        $reseller = $klant->reseller;

        if ($reseller === null) {
            throw new LogicException("ResellerKlant #{$klant->id} has no Reseller.");
        }

        // A resellerklant-level grant can never widen access beyond what
        // the reseller itself has -- see ResellerKlantCourseAccessChecker.
        // Enforced here too, not just on read, so an invalid grant can
        // never be created in the first place.
        if (! $this->resellerAccess->resellerHasAccess($reseller, $course)) {
            throw ResellerLacksCourseAccessException::forReseller($klant->reseller_id, $course->id);
        }

        return ResellerKlantCourseGrant::query()->create([
            'reseller_id' => $klant->reseller_id,
            'resellerklant_id' => $klant->id,
            'course_id' => $course->id,
            'granted_by_user_id' => $data->grantedByUserId,
            'granted_at' => now(),
        ]);
    }
}
