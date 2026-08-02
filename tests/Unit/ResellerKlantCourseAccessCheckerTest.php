<?php

declare(strict_types=1);

use App\Enums\ResellerKlantCourseAccessReason;
use App\Models\Course;
use App\Models\CourseAccessGrant;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\ResellerKlantCourseGrant;
use App\Services\Access\ResellerKlantCourseAccessChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function resellerKlantCourseAccessChecker(): ResellerKlantCourseAccessChecker
{
    return app(ResellerKlantCourseAccessChecker::class);
}

it('denies access when the reseller itself has no access to the course', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->create();

    $result = resellerKlantCourseAccessChecker()->explain($klant, $course);

    expect($result->hasAccess)->toBeFalse()
        ->and($result->reason)->toBe(ResellerKlantCourseAccessReason::ResellerLacksAccess);
});

it('denies access when the reseller has access but has not granted the klant this course', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->forReseller($reseller->id)->create();

    $result = resellerKlantCourseAccessChecker()->explain($klant, $course);

    expect($result->hasAccess)->toBeFalse()
        ->and($result->reason)->toBe(ResellerKlantCourseAccessReason::NotGrantedToKlant);
});

it('grants access when the reseller has access and has granted it to the klant', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->forReseller($reseller->id)->create();
    $grant = ResellerKlantCourseGrant::factory()->create([
        'reseller_id' => $reseller->id,
        'resellerklant_id' => $klant->id,
        'course_id' => $course->id,
    ]);

    $result = resellerKlantCourseAccessChecker()->explain($klant, $course);

    expect($result->hasAccess)->toBeTrue()
        ->and($result->reason)->toBe(ResellerKlantCourseAccessReason::GrantedToKlant)
        ->and($result->grantId)->toBe($grant->id);
});

it('never lets a resellerklant grant widen access beyond a platform grant the reseller lost', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->create();
    $platformGrant = CourseAccessGrant::factory()->revoked()->create(['reseller_id' => $reseller->id, 'course_id' => $course->id]);
    ResellerKlantCourseGrant::factory()->create([
        'reseller_id' => $reseller->id,
        'resellerklant_id' => $klant->id,
        'course_id' => $course->id,
    ]);

    // The platform revoked the reseller's own access after the
    // resellerklant grant was already made -- the resellerklant grant
    // being active must not matter, since the reseller-level check is
    // always evaluated first.
    expect($platformGrant->isRevoked())->toBeTrue();
    expect(resellerKlantCourseAccessChecker()->canAssign($klant, $course))->toBeFalse();
});

it('ignores a revoked resellerklant grant', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->forReseller($reseller->id)->create();
    ResellerKlantCourseGrant::factory()->revoked()->create([
        'reseller_id' => $reseller->id,
        'resellerklant_id' => $klant->id,
        'course_id' => $course->id,
    ]);

    expect(resellerKlantCourseAccessChecker()->canAssign($klant, $course))->toBeFalse();
});
