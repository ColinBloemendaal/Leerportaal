<?php

declare(strict_types=1);

use App\Contracts\Storage\StorageMetering;
use App\Models\Invoice;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeStorageMetering;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('records overage charges for every reseller over the limit and reports the count', function (): void {
    config(['billing.storage_overage_cents_per_gb' => 100]);
    $storage = new FakeStorageMetering;
    $this->app->instance(StorageMetering::class, $storage);

    $overLimit = Reseller::factory()->create();
    $underLimit = Reseller::factory()->create();
    $storage->setUsageBytes($overLimit->id, 6 * 1024 * 1024 * 1024);
    $storage->setUsageBytes($underLimit->id, 1 * 1024 * 1024 * 1024);

    $this->artisan('billing:storage-overage')
        ->expectsOutput('Recorded storage overage charges for 1 reseller(s).')
        ->assertExitCode(0);

    expect(Invoice::query()->withoutTenantScope()->where('reseller_id', $overLimit->id)->exists())->toBeTrue()
        ->and(Invoice::query()->withoutTenantScope()->where('reseller_id', $underLimit->id)->exists())->toBeFalse();
});
