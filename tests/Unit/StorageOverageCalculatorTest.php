<?php

declare(strict_types=1);

use App\Contracts\Storage\StorageMetering;
use App\Services\Billing\StorageOverageCalculator;
use Tests\Fakes\FakeStorageMetering;
use Tests\TestCase;

uses(TestCase::class);

it('charges nothing when usage is at or under the included amount', function (): void {
    $storage = new FakeStorageMetering;
    $this->app->instance(StorageMetering::class, $storage);
    $storage->setUsageBytes(1, 5 * 1024 * 1024 * 1024);

    expect(app(StorageOverageCalculator::class)->overageChargeCents(1))->toBe(0);
});

it('rounds overage up to the next whole gigabyte and applies the configured rate', function (): void {
    config(['billing.storage_overage_cents_per_gb' => 100]);
    $storage = new FakeStorageMetering;
    $this->app->instance(StorageMetering::class, $storage);
    // 5 GB included + 1.5 GB over -> rounds up to 2 GB of overage.
    $storage->setUsageBytes(1, (5 * 1024 * 1024 * 1024) + (int) (1.5 * 1024 * 1024 * 1024));

    expect(app(StorageOverageCalculator::class)->overageChargeCents(1))->toBe(200);
});
