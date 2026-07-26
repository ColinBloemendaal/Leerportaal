<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Policies\ResellerKlantPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new ResellerKlantPolicy;
});

it('allows reseller-side users to view and create klanten', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue();
});

it('denies platform staff from viewing or creating klanten', function (): void {
    $staff = User::factory()->platformStaff()->create();

    expect($this->policy->viewAny($staff))->toBeFalse()
        ->and($this->policy->create($staff))->toBeFalse();
});

it('allows deleting and restoring a klant that belongs to the same reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id]);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    expect($this->policy->delete($user, $klant))->toBeTrue()
        ->and($this->policy->restore($user, $klant))->toBeTrue();
});

it('denies deleting and restoring a klant that belongs to a different reseller', function (): void {
    $user = User::factory()->create();
    $otherReseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->for($otherReseller, 'reseller')->create();

    expect($this->policy->delete($user, $klant))->toBeFalse()
        ->and($this->policy->restore($user, $klant))->toBeFalse();
});
