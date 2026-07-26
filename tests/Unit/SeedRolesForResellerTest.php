<?php

declare(strict_types=1);

use App\Actions\Permissions\SeedRolesForReseller;
use App\Enums\Role;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates every team role for the reseller', function (): void {
    $reseller = Reseller::factory()->create();

    (new SeedRolesForReseller)($reseller);

    foreach (Role::teamRoles() as $role) {
        expect(RoleModel::query()->where(['name' => $role->value, 'reseller_id' => $reseller->id])->exists())
            ->toBeTrue();
    }

    expect(RoleModel::query()->where('reseller_id', $reseller->id)->count())->toBe(count(Role::teamRoles()));
});

it('does not duplicate roles when run twice', function (): void {
    $reseller = Reseller::factory()->create();

    (new SeedRolesForReseller)($reseller);
    (new SeedRolesForReseller)($reseller);

    expect(RoleModel::query()->where('reseller_id', $reseller->id)->count())->toBe(count(Role::teamRoles()));
});

it('keeps roles isolated per reseller', function (): void {
    // Both resellers already got auto-seeded via the created event --
    // re-running for A must not touch B's rows.
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    (new SeedRolesForReseller)($resellerA);

    expect(RoleModel::query()->where('reseller_id', $resellerA->id)->count())->toBe(count(Role::teamRoles()))
        ->and(RoleModel::query()->where('reseller_id', $resellerB->id)->count())->toBe(count(Role::teamRoles()))
        ->and(RoleModel::query()->count())->toBe(count(Role::teamRoles()) * 2);
});

it('automatically seeds roles when a reseller is created', function (): void {
    $reseller = Reseller::factory()->create();

    expect(RoleModel::query()->where('reseller_id', $reseller->id)->count())->toBe(count(Role::teamRoles()));
});
