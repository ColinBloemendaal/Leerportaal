<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a resellerklant', function (): void {
    $resellerKlant = ResellerKlant::factory()->create();
    $group = Group::factory()->for($resellerKlant, 'resellerKlant')->create(['reseller_id' => $resellerKlant->reseller_id]);

    // ResellerKlant is itself TenantScoped, so loading the relation
    // needs the matching tenant resolved -- same as any other
    // cross-tenant-scoped-model relation load in this codebase.
    app(TenantContext::class)->set($resellerKlant->reseller);

    expect($group->resellerKlant->is($resellerKlant))->toBeTrue();
});

it('has many members', function (): void {
    $group = Group::factory()->create();
    app(TenantContext::class)->set($group->reseller);

    $members = User::factory()->count(3)->create();
    $group->members()->attach($members->pluck('id'));

    expect($group->members()->count())->toBe(3)
        ->and($members->first()->groups()->count())->toBe(1);
});
