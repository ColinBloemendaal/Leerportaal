<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new UserPolicy;
});

it('allows reseller-side users to view the user list', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('denies platform staff from viewing the user list', function (): void {
    $staff = User::factory()->platformStaff()->create();

    expect($this->policy->viewAny($staff))->toBeFalse();
});

it('allows viewing and updating a user in the same reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $admin = User::factory()->create(['reseller_id' => $reseller->id]);
    $target = User::factory()->create(['reseller_id' => $reseller->id]);

    expect($this->policy->view($admin, $target))->toBeTrue()
        ->and($this->policy->update($admin, $target))->toBeTrue();
});

it('denies viewing a user in a different reseller', function (): void {
    $admin = User::factory()->create();
    $target = User::factory()->create();

    expect($this->policy->view($admin, $target))->toBeFalse();
});

it('allows deleting another user in the same reseller but not oneself', function (): void {
    $reseller = Reseller::factory()->create();
    $admin = User::factory()->create(['reseller_id' => $reseller->id]);
    $target = User::factory()->create(['reseller_id' => $reseller->id]);

    expect($this->policy->delete($admin, $target))->toBeTrue()
        ->and($this->policy->delete($admin, $admin))->toBeFalse();
});

it('allows platform staff to erase any user across resellers', function (): void {
    $staff = User::factory()->platformStaff()->create();
    $target = User::factory()->create();

    expect($this->policy->erase($staff, $target))->toBeTrue();
});

it('allows reseller-side users to erase within their own reseller only', function (): void {
    $reseller = Reseller::factory()->create();
    $admin = User::factory()->create(['reseller_id' => $reseller->id]);
    $sameReseller = User::factory()->create(['reseller_id' => $reseller->id]);
    $otherReseller = User::factory()->create();

    expect($this->policy->erase($admin, $sameReseller))->toBeTrue()
        ->and($this->policy->erase($admin, $otherReseller))->toBeFalse();
});

it('never allows erasing yourself', function (): void {
    $staff = User::factory()->platformStaff()->create();

    expect($this->policy->erase($staff, $staff))->toBeFalse();
});
