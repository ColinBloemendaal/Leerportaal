<?php

declare(strict_types=1);

use App\Contracts\Repositories\MediaRepository;
use App\Models\Media;
use App\Models\Reseller;
use App\Tenancy\TenantContext;

/**
 * Media has no TenantScope (see the model's docblock), so it doesn't fit
 * the standard assertTenantIsolated() pattern -- mirrors
 * tests/Tenancy/CourseVisibilityTest.php.
 */
it('shows platform media to every reseller, and reseller media only to their owner', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    $platform = Media::factory()->create();
    $ownedByA = Media::factory()->forReseller($resellerA->id)->create();
    Media::factory()->forReseller($resellerB->id)->create();

    app(TenantContext::class)->set($resellerA);
    $visibleToA = app(MediaRepository::class)->visibleToCurrentReseller();

    expect($visibleToA->pluck('id')->sort()->values()->all())
        ->toBe(collect([$platform->id, $ownedByA->id])->sort()->values()->all());
});

it('shows only platform media when no tenant is resolved', function (): void {
    $reseller = Reseller::factory()->create();
    Media::factory()->forReseller($reseller->id)->create();
    $platform = Media::factory()->create();

    $visible = app(MediaRepository::class)->visibleToCurrentReseller();

    expect($visible->pluck('id')->all())->toBe([$platform->id]);
});
