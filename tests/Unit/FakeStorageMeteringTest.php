<?php

declare(strict_types=1);

use Tests\Fakes\FakeStorageMetering;

it('reports zero usage for a reseller with no set usage', function (): void {
    $metering = new FakeStorageMetering;

    expect($metering->usageBytes(1))->toBe(0)
        ->and($metering->isOverLimit(1))->toBeFalse();
});

it('reports whatever usage was set for that reseller', function (): void {
    $metering = new FakeStorageMetering;
    $metering->setUsageBytes(1, 1234);

    expect($metering->usageBytes(1))->toBe(1234)
        ->and($metering->usageBytes(2))->toBe(0);
});

it('is over limit once usage exceeds the included allowance', function (): void {
    $metering = new FakeStorageMetering;
    $metering->setUsageBytes(1, 6 * 1024 * 1024 * 1024);

    expect($metering->isOverLimit(1))->toBeTrue();
});
