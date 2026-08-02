<?php

declare(strict_types=1);

use App\Models\CourseAccessGrant;
use App\Models\Reseller;
use App\Repositories\Eloquent\EloquentCourseAccessGrantRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns only active grants for the given reseller, regardless of ambient tenant context', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    $active = CourseAccessGrant::factory()->create(['reseller_id' => $resellerA->id]);
    CourseAccessGrant::factory()->revoked()->create(['reseller_id' => $resellerA->id]);
    CourseAccessGrant::factory()->create(['reseller_id' => $resellerB->id]);

    // No ambient TenantContext is set at all in this test -- the
    // repository must still find resellerA's grant, proving it bypasses
    // the fails-closed TenantScope rather than depending on it.
    $result = (new EloquentCourseAccessGrantRepository)->activeGrantsForReseller($resellerA->id);

    expect($result)->toHaveCount(1);
    expect($result->first()->is($active))->toBeTrue();
});
