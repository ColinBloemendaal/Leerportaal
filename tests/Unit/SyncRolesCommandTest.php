<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as RoleModel;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('backfills roles for resellers missing them and reports a count', function (): void {
    $reseller = Reseller::factory()->create();

    // Simulates a reseller that existed before the created-event wiring.
    RoleModel::query()->where('reseller_id', $reseller->id)->delete();

    $this->artisan('permissions:sync-roles')
        ->expectsOutputToContain('Synced roles for 1 reseller(s).')
        ->assertExitCode(0);

    expect(RoleModel::query()->where('reseller_id', $reseller->id)->count())->toBe(count(Role::teamRoles()));
});

it('is safe to run when every reseller already has its roles', function (): void {
    Reseller::factory()->create();

    $this->artisan('permissions:sync-roles')->assertExitCode(0);
    $this->artisan('permissions:sync-roles')->assertExitCode(0);
});
