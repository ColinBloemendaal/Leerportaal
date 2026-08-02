<?php

declare(strict_types=1);

use App\Contracts\Repositories\CourseRepository;
use App\Models\Course;
use App\Models\Reseller;
use App\Tenancy\TenantContext;

/**
 * Course has no TenantScope (see the model's docblock), so it doesn't fit
 * the standard assertTenantIsolated() pattern -- this is the custom
 * equivalent for a mixed platform/reseller-owned table, mirroring
 * tests/Tenancy/CourseCategoryVisibilityTest.php.
 */
it('shows platform courses to every reseller, and reseller courses only to their owner', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    $platform = Course::factory()->create();
    $ownedByA = Course::factory()->forReseller($resellerA->id)->create();
    Course::factory()->forReseller($resellerB->id)->create();

    app(TenantContext::class)->set($resellerA);
    $visibleToA = app(CourseRepository::class)->visibleToCurrentReseller();

    expect($visibleToA->pluck('id')->sort()->values()->all())
        ->toBe(collect([$platform->id, $ownedByA->id])->sort()->values()->all());
});

it('shows only platform courses when no tenant is resolved', function (): void {
    $reseller = Reseller::factory()->create();
    Course::factory()->forReseller($reseller->id)->create();
    $platform = Course::factory()->create();

    $visible = app(CourseRepository::class)->visibleToCurrentReseller();

    expect($visible->pluck('id')->all())->toBe([$platform->id]);
});
