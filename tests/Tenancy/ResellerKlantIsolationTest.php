<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Models\ResellerKlant;
use App\Tenancy\TenantContext;

beforeEach(function (): void {
    $this->resellerA = Reseller::factory()->create();
    $this->resellerB = Reseller::factory()->create();

    $this->klantA = ResellerKlant::factory()->for($this->resellerA, 'reseller')->create();
    $this->klantB = ResellerKlant::factory()->for($this->resellerB, 'reseller')->create();

    app(TenantContext::class)->set($this->resellerA);
});

it('hides reseller B rows on index', function (): void {
    $visible = ResellerKlant::all();

    expect($visible)->toHaveCount(1)
        ->and($visible->first()->is($this->klantA))->toBeTrue();
});

it('hides reseller B rows on show', function (): void {
    expect(ResellerKlant::find($this->klantA->id))->not->toBeNull()
        ->and(ResellerKlant::find($this->klantB->id))->toBeNull();
});

it('cannot update reseller B rows', function (): void {
    $affected = ResellerKlant::where('id', $this->klantB->id)->update(['name' => 'renamed']);

    expect($affected)->toBe(0);

    // Admin/platform context: verifying the row was untouched.
    $unchanged = ResellerKlant::query()->withoutTenantScope()->find($this->klantB->id);
    expect($unchanged->name)->not->toBe('renamed');
});

it('cannot delete reseller B rows', function (): void {
    $deleted = ResellerKlant::where('id', $this->klantB->id)->delete();

    expect($deleted)->toBe(0);

    $stillThere = ResellerKlant::query()->withoutTenantScope()->find($this->klantB->id);
    expect($stillThere)->not->toBeNull();
});
