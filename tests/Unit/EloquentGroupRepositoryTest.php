<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\Reseller;
use App\Repositories\Eloquent\EloquentGroupRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('lists only the current reseller\'s own groups', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $ownGroup = Group::factory()->for($reseller)->create(['name' => 'Own group']);

    app(TenantContext::class)->set($otherReseller);
    Group::factory()->for($otherReseller)->create(['name' => 'Other group']);

    app(TenantContext::class)->set($reseller);
    $found = app(EloquentGroupRepository::class)->forCurrentReseller();

    expect($found->pluck('id')->all())->toBe([$ownGroup->id]);
});

it('finds a group by id, scoped to the current tenant', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();

    app(TenantContext::class)->set($otherReseller);
    $otherGroup = Group::factory()->for($otherReseller)->create();

    app(TenantContext::class)->set($reseller);

    expect(app(EloquentGroupRepository::class)->findForCurrentReseller($otherGroup->id))->toBeNull();
});
