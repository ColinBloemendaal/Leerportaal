<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\ResellerThemePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new ResellerThemePolicy;
});

it('allows reseller-side users to view and update their theme', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->update($user))->toBeTrue();
});

it('denies platform staff from viewing or updating a theme', function (): void {
    $staff = User::factory()->platformStaff()->create();

    expect($this->policy->viewAny($staff))->toBeFalse()
        ->and($this->policy->update($staff))->toBeFalse();
});
