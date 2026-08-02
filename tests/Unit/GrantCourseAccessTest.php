<?php

declare(strict_types=1);

use App\Actions\Access\GrantCourseAccess;
use App\DataTransferObjects\Access\GrantCourseAccessData;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function grantCourseAccessAction(): GrantCourseAccess
{
    return app(GrantCourseAccess::class);
}

it('creates a direct course grant', function (): void {
    $reseller = Reseller::factory()->create();
    $course = Course::factory()->create();
    $admin = User::factory()->create();

    $grant = grantCourseAccessAction()(new GrantCourseAccessData($reseller->id, $course->id, null, $admin->id));

    expect($grant->reseller_id)->toBe($reseller->id)
        ->and($grant->course_id)->toBe($course->id)
        ->and($grant->course_category_id)->toBeNull()
        ->and($grant->granted_by_user_id)->toBe($admin->id)
        ->and($grant->granted_at)->not->toBeNull()
        ->and($grant->revoked_at)->toBeNull();
});

it('creates a category grant', function (): void {
    $reseller = Reseller::factory()->create();
    $category = CourseCategory::factory()->create();

    $grant = grantCourseAccessAction()(new GrantCourseAccessData($reseller->id, null, $category->id));

    expect($grant->course_category_id)->toBe($category->id)
        ->and($grant->course_id)->toBeNull();
});
