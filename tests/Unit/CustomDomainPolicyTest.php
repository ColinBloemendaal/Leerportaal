<?php

declare(strict_types=1);

use App\Models\CustomDomain;
use App\Models\Reseller;
use App\Models\User;
use App\Policies\CustomDomainPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new CustomDomainPolicy;
});

it('allows reseller-side users to view and create custom domains', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue();
});

it('denies platform staff from viewing or creating custom domains', function (): void {
    $staff = User::factory()->platformStaff()->create();

    expect($this->policy->viewAny($staff))->toBeFalse()
        ->and($this->policy->create($staff))->toBeFalse();
});

it('allows deleting a custom domain that belongs to the same reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id]);
    $domain = CustomDomain::factory()->create(['reseller_id' => $reseller->id]);

    expect($this->policy->delete($user, $domain))->toBeTrue();
});

it('denies deleting a custom domain that belongs to a different reseller', function (): void {
    $user = User::factory()->create();
    $otherReseller = Reseller::factory()->create();
    $domain = CustomDomain::factory()->create(['reseller_id' => $otherReseller->id]);

    expect($this->policy->delete($user, $domain))->toBeFalse();
});
