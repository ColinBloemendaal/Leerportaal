<?php

declare(strict_types=1);

namespace Tests\Tenancy;

use App\Models\Reseller;
use App\Tenancy\TenantContext;

trait InteractsWithTenancy
{
    protected function actingAsReseller(Reseller $reseller): static
    {
        app(TenantContext::class)->set($reseller);

        return $this;
    }

    /**
     * Full isolation pattern from CLAUDE.md §8: create rows for reseller A
     * and B, act as A, assert B's rows are invisible on index, show,
     * update, and delete.
     *
     * @param  class-string  $modelClass
     */
    protected function assertTenantIsolated(string $modelClass): void
    {
        $resellerA = Reseller::factory()->create();
        $resellerB = Reseller::factory()->create();

        $rowA = $modelClass::factory()->create(['reseller_id' => $resellerA->id]);
        $rowB = $modelClass::factory()->create(['reseller_id' => $resellerB->id]);

        $this->actingAsReseller($resellerA);

        // index
        $visible = $modelClass::all();
        expect($visible)->toHaveCount(1);
        expect($visible->first()->is($rowA))->toBeTrue();

        // show
        expect($modelClass::find($rowA->getKey()))->not->toBeNull();
        expect($modelClass::find($rowB->getKey()))->toBeNull();

        // update
        $updated = $modelClass::query()->where($rowB->getKeyName(), $rowB->getKey())->update(['updated_at' => now()]);
        expect($updated)->toBe(0);

        // delete
        $deleted = $modelClass::query()->where($rowB->getKeyName(), $rowB->getKey())->delete();
        expect($deleted)->toBe(0);
    }
}
