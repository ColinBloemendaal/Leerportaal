<?php

declare(strict_types=1);

use App\Actions\Access\GrantCourseToResellerKlant;
use App\DataTransferObjects\Access\GrantCourseToResellerKlantData;
use App\Exceptions\ResellerLacksCourseAccessException;
use App\Models\Course;
use App\Models\CourseAccessGrant;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function grantCourseToResellerKlantAction(): GrantCourseToResellerKlant
{
    return app(GrantCourseToResellerKlant::class);
}

it('grants a reseller-owned course to its own resellerklant', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->forReseller($reseller->id)->create();
    $admin = User::factory()->create();

    $grant = grantCourseToResellerKlantAction()(
        new GrantCourseToResellerKlantData($klant->id, $course->id, $admin->id),
    );

    expect($grant->reseller_id)->toBe($reseller->id)
        ->and($grant->resellerklant_id)->toBe($klant->id)
        ->and($grant->course_id)->toBe($course->id)
        ->and($grant->granted_by_user_id)->toBe($admin->id);
});

it('grants a catalog course the reseller was itself granted access to', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->create();
    CourseAccessGrant::factory()->create(['reseller_id' => $reseller->id, 'course_id' => $course->id]);

    $grant = grantCourseToResellerKlantAction()(new GrantCourseToResellerKlantData($klant->id, $course->id));

    expect($grant->course_id)->toBe($course->id);
});

it('refuses to grant a catalog course the reseller has no access to', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->create();

    grantCourseToResellerKlantAction()(new GrantCourseToResellerKlantData($klant->id, $course->id));
})->throws(ResellerLacksCourseAccessException::class);

it('refuses to grant a course owned by a different reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->create(['reseller_id' => $reseller->id]);
    $course = Course::factory()->forReseller($otherReseller->id)->create();

    grantCourseToResellerKlantAction()(new GrantCourseToResellerKlantData($klant->id, $course->id));
})->throws(ResellerLacksCourseAccessException::class);
