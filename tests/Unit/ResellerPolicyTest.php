<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\User;
use App\Policies\ResellerPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new ResellerPolicy;
});

it('allows platform staff to view and manage resellers', function (): void {
    $staff = User::factory()->platformStaff()->create();
    $reseller = Reseller::factory()->create();

    expect($this->policy->viewAny($staff))->toBeTrue()
        ->and($this->policy->view($staff, $reseller))->toBeTrue()
        ->and($this->policy->update($staff, $reseller))->toBeTrue();
});

it('denies reseller-side users from managing resellers', function (): void {
    $user = User::factory()->create();
    $reseller = Reseller::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse()
        ->and($this->policy->view($user, $reseller))->toBeFalse()
        ->and($this->policy->update($user, $reseller))->toBeFalse();
});
