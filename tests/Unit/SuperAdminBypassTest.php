<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\ResellerKlant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lets a super admin do anything, bypassing normal policy checks', function (): void {
    $superAdmin = User::factory()->platformRole(Role::SuperAdmin)->create();
    $klant = ResellerKlant::factory()->create();

    // A platform-staff super admin has no reseller_id, so ResellerKlantPolicy's
    // own check would normally deny this outright.
    expect(Gate::forUser($superAdmin)->allows('create', ResellerKlant::class))->toBeTrue()
        ->and(Gate::forUser($superAdmin)->allows('viewAny', ResellerKlant::class))->toBeTrue();
});

it('does not grant anything to platform staff without the super-admin role', function (): void {
    $support = User::factory()->platformRole(Role::Support)->create();

    expect(Gate::forUser($support)->allows('viewAny', ResellerKlant::class))->toBeFalse();
});

it('does not affect normal reseller-side authorization', function (): void {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', ResellerKlant::class))->toBeTrue();
});
