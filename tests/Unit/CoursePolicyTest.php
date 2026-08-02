<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\User;
use App\Policies\CoursePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new CoursePolicy;
});

it('allows any authenticated user to view or create courses', function (): void {
    $user = User::factory()->create();

    expect($this->policy->view($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue();
});

it('denies a reseller from writing to a catalog (platform-owned) course', function (): void {
    $reseller = User::factory()->create();
    $catalogCourse = Course::factory()->create();

    expect($this->policy->update($reseller, $catalogCourse))->toBeFalse()
        ->and($this->policy->delete($reseller, $catalogCourse))->toBeFalse()
        ->and($this->policy->restore($reseller, $catalogCourse))->toBeFalse();
});

it('allows platform staff to write to a catalog course', function (): void {
    $staff = User::factory()->platformStaff()->create();
    $catalogCourse = Course::factory()->create();

    expect($this->policy->update($staff, $catalogCourse))->toBeTrue()
        ->and($this->policy->delete($staff, $catalogCourse))->toBeTrue()
        ->and($this->policy->restore($staff, $catalogCourse))->toBeTrue();
});

it('allows a reseller to write to their own course', function (): void {
    $user = User::factory()->create();
    $ownCourse = Course::factory()->forReseller($user->reseller_id)->create();

    expect($this->policy->update($user, $ownCourse))->toBeTrue()
        ->and($this->policy->delete($user, $ownCourse))->toBeTrue();
});

it('denies a reseller from writing to another reseller\'s course', function (): void {
    $user = User::factory()->create();
    $otherCourse = Course::factory()->forReseller()->create();

    expect($this->policy->update($user, $otherCourse))->toBeFalse()
        ->and($this->policy->delete($user, $otherCourse))->toBeFalse();
});

it('denies platform staff from writing to a reseller-owned course', function (): void {
    $staff = User::factory()->platformStaff()->create();
    $resellerCourse = Course::factory()->forReseller()->create();

    expect($this->policy->update($staff, $resellerCourse))->toBeFalse();
});
