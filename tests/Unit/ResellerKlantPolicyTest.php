<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Policies\ResellerKlantPolicy;
use App\Tenancy\TenantContext;
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

it('lets reseller staff (no resellerklant_id of their own) view any klant in their reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => null]);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    expect($this->policy->view($staff, $klant))->toBeTrue();
});

it('denies reseller staff from viewing a klant in a different reseller', function (): void {
    $user = User::factory()->create(['resellerklant_id' => null]);
    $otherReseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->for($otherReseller, 'reseller')->create();

    expect($this->policy->view($user, $klant))->toBeFalse();
});

it('lets a klant-admin view their own klant', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $klantAdmin = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    $klantAdmin->assignRole('klant-admin');

    expect($this->policy->view($klantAdmin, $klant))->toBeTrue();
});

it('denies a klant-admin from viewing a different klant in the same reseller', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $otherKlant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $klantAdmin = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    $klantAdmin->assignRole('klant-admin');

    expect($this->policy->view($klantAdmin, $otherKlant))->toBeFalse();
});

it('denies a plain cursist from viewing their own klant\'s dashboard', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    expect($this->policy->view($cursist, $klant))->toBeFalse();
});
