<?php

declare(strict_types=1);

use App\Contracts\Repositories\CourseCategoryRepository;
use App\Models\CourseCategory;
use App\Models\Reseller;
use App\Tenancy\TenantContext;

/**
 * CourseCategory has no TenantScope (see the model's docblock), so it
 * doesn't fit the standard assertTenantIsolated() pattern used for
 * strictly tenant-owned models -- this is the custom equivalent for a
 * mixed platform/reseller-owned table.
 */
it('shows platform categories to every reseller, and reseller categories only to their owner', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    $platform = CourseCategory::factory()->create(['name' => 'Platform category']);
    $ownedByA = CourseCategory::factory()->create(['name' => 'A only', 'reseller_id' => $resellerA->id]);
    $ownedByB = CourseCategory::factory()->create(['name' => 'B only', 'reseller_id' => $resellerB->id]);

    app(TenantContext::class)->set($resellerA);
    $visibleToA = app(CourseCategoryRepository::class)->visibleToCurrentReseller();

    expect($visibleToA->pluck('id')->sort()->values()->all())
        ->toBe(collect([$platform->id, $ownedByA->id])->sort()->values()->all())
        ->and($visibleToA->pluck('id'))->not->toContain($ownedByB->id);
});

it('excludes another reseller\'s categories', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();
    CourseCategory::factory()->create(['reseller_id' => $resellerB->id]);

    app(TenantContext::class)->set($resellerA);
    $visible = app(CourseCategoryRepository::class)->visibleToCurrentReseller();

    expect($visible->where('reseller_id', $resellerB->id))->toHaveCount(0);
});

it('shows only platform categories when no tenant is resolved', function (): void {
    $reseller = Reseller::factory()->create();
    CourseCategory::factory()->create(['reseller_id' => $reseller->id]);
    $platform = CourseCategory::factory()->create();

    $visible = app(CourseCategoryRepository::class)->visibleToCurrentReseller();

    expect($visible->pluck('id')->all())->toBe([$platform->id]);
});
