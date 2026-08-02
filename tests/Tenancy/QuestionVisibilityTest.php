<?php

declare(strict_types=1);

use App\Contracts\Repositories\QuestionRepository;
use App\Models\Question;
use App\Models\Reseller;
use App\Tenancy\TenantContext;

/**
 * Question has no TenantScope (see the model's docblock), so it doesn't
 * fit the standard assertTenantIsolated() pattern -- mirrors
 * tests/Tenancy/MediaVisibilityTest.php.
 */
it('shows platform bank questions to every reseller, and reseller questions only to their owner', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    $platform = Question::factory()->create();
    $ownedByA = Question::factory()->forReseller($resellerA->id)->create();
    Question::factory()->forReseller($resellerB->id)->create();

    app(TenantContext::class)->set($resellerA);
    $visibleToA = app(QuestionRepository::class)->visibleToCurrentReseller();

    expect($visibleToA->pluck('id')->sort()->values()->all())
        ->toBe(collect([$platform->id, $ownedByA->id])->sort()->values()->all());
});

it('shows only platform bank questions when no tenant is resolved', function (): void {
    $reseller = Reseller::factory()->create();
    Question::factory()->forReseller($reseller->id)->create();
    $platform = Question::factory()->create();

    $visible = app(QuestionRepository::class)->visibleToCurrentReseller();

    expect($visible->pluck('id')->all())->toBe([$platform->id]);
});
