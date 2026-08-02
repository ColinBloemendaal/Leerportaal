<?php

declare(strict_types=1);

use App\Actions\Access\RevokeCourseFromResellerKlant;
use App\Models\ResellerKlantCourseGrant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('sets revoked_at without deleting the grant', function (): void {
    $grant = ResellerKlantCourseGrant::factory()->create();

    $revoked = app(RevokeCourseFromResellerKlant::class)($grant);

    expect($revoked->revoked_at)->not->toBeNull()
        ->and($revoked->isRevoked())->toBeTrue()
        ->and(ResellerKlantCourseGrant::query()->withoutTenantScope()->find($grant->id))->not->toBeNull();
});
