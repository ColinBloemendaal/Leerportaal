<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\Reseller;
use App\Models\User;
use App\Policies\CoursePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new CoursePolicy;
});

it('allows any authenticated user to view a course', function (): void {
    $user = User::factory()->create();

    expect($this->policy->view($user))->toBeTrue();
});

it('allows platform staff to create courses regardless of any authoring add-on', function (): void {
    $staff = User::factory()->platformStaff()->create();

    expect($this->policy->create($staff))->toBeTrue();
});

it('allows a reseller user to create a course when their reseller has an active authoring add-on', function (): void {
    $reseller = Reseller::factory()->withAuthoringAddon()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    expect($this->policy->create($user))->toBeTrue();
});

it('denies a reseller user from creating a course when their reseller has no authoring add-on', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    expect($this->policy->create($user))->toBeFalse();
});

it('denies a reseller user from creating a course once the authoring add-on has lapsed', function (): void {
    $reseller = Reseller::factory()->create(['authoring_addon_expires_at' => now()->subDay()]);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    expect($this->policy->create($user))->toBeFalse();
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
