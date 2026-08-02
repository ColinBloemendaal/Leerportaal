<?php

declare(strict_types=1);

use App\Models\ResellerKlant;
use App\Models\ResellerKlantCourseGrant;
use App\Repositories\Eloquent\EloquentResellerKlantCourseGrantRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns only active grants for the given resellerklant, regardless of ambient tenant context', function (): void {
    $klantA = ResellerKlant::factory()->create();
    $klantB = ResellerKlant::factory()->create();

    $active = ResellerKlantCourseGrant::factory()->create(['resellerklant_id' => $klantA->id, 'reseller_id' => $klantA->reseller_id]);
    ResellerKlantCourseGrant::factory()->revoked()->create(['resellerklant_id' => $klantA->id, 'reseller_id' => $klantA->reseller_id]);
    ResellerKlantCourseGrant::factory()->create(['resellerklant_id' => $klantB->id, 'reseller_id' => $klantB->reseller_id]);

    $result = (new EloquentResellerKlantCourseGrantRepository)->activeGrantsForResellerKlant($klantA->id);

    expect($result)->toHaveCount(1);
    expect($result->first()->is($active))->toBeTrue();
});
