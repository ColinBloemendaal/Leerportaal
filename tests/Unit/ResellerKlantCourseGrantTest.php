<?php

declare(strict_types=1);

use App\Models\ResellerKlantCourseGrant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('reports whether it has been revoked', function (): void {
    $active = ResellerKlantCourseGrant::factory()->create();
    $revoked = ResellerKlantCourseGrant::factory()->revoked()->create();

    expect($active->isRevoked())->toBeFalse()
        ->and($revoked->isRevoked())->toBeTrue();
});

it('resolves its resellerklant relation despite the no-underscore column name', function (): void {
    $grant = ResellerKlantCourseGrant::factory()->create();

    // withoutTenantScope(): the factory-default klant's own reseller_id
    // and the grant's reseller_id default independently (see the
    // factory's comment), so they won't generally match -- irrelevant
    // to what this test is actually proving, which is that the relation
    // reads the correct (no-underscore) FK column at all.
    $klant = $grant->resellerKlant()->withoutTenantScope()->first();

    expect($klant?->id)->toBe($grant->resellerklant_id);
});
