<?php

declare(strict_types=1);

use App\Models\CustomDomain;
use App\Models\Reseller;
use App\Tenancy\TenantContext;

beforeEach(function (): void {
    $this->resellerA = Reseller::factory()->create();
    $this->resellerB = Reseller::factory()->create();

    $this->domainA = CustomDomain::factory()->for($this->resellerA, 'reseller')->create();
    $this->domainB = CustomDomain::factory()->for($this->resellerB, 'reseller')->create();

    app(TenantContext::class)->set($this->resellerA);
});

it('hides reseller B rows on index', function (): void {
    $visible = CustomDomain::all();

    expect($visible)->toHaveCount(1)
        ->and($visible->first()->is($this->domainA))->toBeTrue();
});

it('hides reseller B rows on show', function (): void {
    expect(CustomDomain::find($this->domainA->id))->not->toBeNull()
        ->and(CustomDomain::find($this->domainB->id))->toBeNull();
});

it('cannot update reseller B rows', function (): void {
    $affected = CustomDomain::where('id', $this->domainB->id)->update(['domain' => 'renamed.test']);

    expect($affected)->toBe(0);
});

it('cannot delete reseller B rows', function (): void {
    $deleted = CustomDomain::where('id', $this->domainB->id)->delete();

    expect($deleted)->toBe(0);

    $stillThere = CustomDomain::query()->withoutTenantScope()->find($this->domainB->id);
    expect($stillThere)->not->toBeNull();
});
