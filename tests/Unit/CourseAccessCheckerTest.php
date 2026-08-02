<?php

declare(strict_types=1);

use App\Enums\CourseAccessReason;
use App\Models\Course;
use App\Models\CourseAccessGrant;
use App\Models\CourseCategory;
use App\Models\Reseller;
use App\Services\Access\CourseAccessChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function courseAccessChecker(): CourseAccessChecker
{
    return app(CourseAccessChecker::class);
}

it('always grants a reseller access to its own courses', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->forReseller($reseller->id)->create();

    $result = courseAccessChecker()->explain($reseller, $course);

    expect($result->hasAccess)->toBeTrue()
        ->and($result->reason)->toBe(CourseAccessReason::OwnedByReseller);
});

it('denies access to a catalog course with no grant', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();

    $result = courseAccessChecker()->explain($reseller, $course);

    expect($result->hasAccess)->toBeFalse()
        ->and($result->reason)->toBe(CourseAccessReason::NoAccess);
});

it('denies access to a course owned by a different reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    $course = Course::factory()->forReseller($otherReseller->id)->create();

    $result = courseAccessChecker()->explain($reseller, $course);

    expect($result->hasAccess)->toBeFalse()
        ->and($result->reason)->toBe(CourseAccessReason::NoAccess);
});

it('grants access via a direct course grant', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $grant = CourseAccessGrant::factory()->create(['reseller_id' => $reseller->id, 'course_id' => $course->id]);

    $result = courseAccessChecker()->explain($reseller, $course);

    expect($result->hasAccess)->toBeTrue()
        ->and($result->reason)->toBe(CourseAccessReason::DirectGrant)
        ->and($result->grantId)->toBe($grant->id);
});

it('grants access via a category grant', function (): void {
    $reseller = Reseller::factory()->create();
    $category = CourseCategory::factory()->create();
    $course = Course::factory()->inCategory($category->id)->create();
    $grant = CourseAccessGrant::factory()->forCategory($category->id)->create(['reseller_id' => $reseller->id]);

    $result = courseAccessChecker()->explain($reseller, $course);

    expect($result->hasAccess)->toBeTrue()
        ->and($result->reason)->toBe(CourseAccessReason::CategoryGrant)
        ->and($result->grantId)->toBe($grant->id);
});

it('cascades a category grant to a course added to the category after the grant was made', function (): void {
    $reseller = Reseller::factory()->create();
    $category = CourseCategory::factory()->create();

    CourseAccessGrant::factory()->forCategory($category->id)->create(['reseller_id' => $reseller->id]);

    // The course is created after the grant already exists -- proves the
    // checker reads category membership live rather than snapshotting it
    // at grant time.
    $newCourse = Course::factory()->inCategory($category->id)->create();

    expect(courseAccessChecker()->resellerHasAccess($reseller, $newCourse))->toBeTrue();
});

it('ignores a revoked grant', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    CourseAccessGrant::factory()->revoked()->create(['reseller_id' => $reseller->id, 'course_id' => $course->id]);

    expect(courseAccessChecker()->resellerHasAccess($reseller, $course))->toBeFalse();
});

it('does not grant access via another reseller\'s grant', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    CourseAccessGrant::factory()->create(['reseller_id' => $otherReseller->id, 'course_id' => $course->id]);

    expect(courseAccessChecker()->resellerHasAccess($reseller, $course))->toBeFalse();
});
