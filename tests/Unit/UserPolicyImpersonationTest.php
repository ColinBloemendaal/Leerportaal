<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->policy = new UserPolicy;
});

it('lets a super admin impersonate any reseller-side user', function (): void {
    $superAdmin = User::factory()->platformRole(Role::SuperAdmin)->create();
    $target = User::factory()->create();

    expect($this->policy->impersonate($superAdmin, $target))->toBeTrue();
});

it('never lets anyone impersonate platform staff', function (): void {
    $superAdmin = User::factory()->platformRole(Role::SuperAdmin)->create();
    $platformTarget = User::factory()->platformStaff()->create();

    expect($this->policy->impersonate($superAdmin, $platformTarget))->toBeFalse();
});

it('never lets a user impersonate themselves', function (): void {
    $superAdmin = User::factory()->platformRole(Role::SuperAdmin)->create();

    expect($this->policy->impersonate($superAdmin, $superAdmin))->toBeFalse();
});

it('lets reseller staff impersonate a klant-side user in their own reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    expect($this->policy->impersonate($staff, $cursist))->toBeTrue();
});

it('does not let reseller staff impersonate fellow reseller staff', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);
    $otherStaff = User::factory()->create(['reseller_id' => $reseller->id]);

    expect($this->policy->impersonate($staff, $otherStaff))->toBeFalse();
});

it('does not let reseller staff impersonate a klant-side user in a different reseller', function (): void {
    $reseller = Reseller::factory()->create();
    $staff = User::factory()->create(['reseller_id' => $reseller->id]);

    $otherReseller = Reseller::factory()->create();
    $otherKlant = ResellerKlant::factory()->for($otherReseller, 'reseller')->create();
    $cursist = User::factory()->create(['reseller_id' => $otherReseller->id, 'resellerklant_id' => $otherKlant->id]);

    expect($this->policy->impersonate($staff, $cursist))->toBeFalse();
});

it('lets a klant-admin impersonate a cursist in their own klant', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $klantAdmin = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    $klantAdmin->assignRole('klant-admin');

    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    expect($this->policy->impersonate($klantAdmin, $cursist))->toBeTrue();
});

it('does not let a plain cursist impersonate anyone', function (): void {
    $reseller = Reseller::factory()->create();
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    $otherCursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);

    expect($this->policy->impersonate($cursist, $otherCursist))->toBeFalse();
});

it('does not let a klant-admin impersonate a cursist in a different klant', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $klant = ResellerKlant::factory()->for($reseller, 'reseller')->create();
    $otherKlant = ResellerKlant::factory()->for($reseller, 'reseller')->create();

    $klantAdmin = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $klant->id]);
    $klantAdmin->assignRole('klant-admin');

    $cursist = User::factory()->create(['reseller_id' => $reseller->id, 'resellerklant_id' => $otherKlant->id]);

    expect($this->policy->impersonate($klantAdmin, $cursist))->toBeFalse();
});
