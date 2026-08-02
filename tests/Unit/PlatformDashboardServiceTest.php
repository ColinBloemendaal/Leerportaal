<?php

declare(strict_types=1);

use App\Contracts\Storage\StorageMetering;
use App\Models\Media;
use App\Models\Reseller;
use App\Services\Reporting\PlatformDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeStorageMetering;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('composes reseller/user/course counts, revenue, and storage into one snapshot', function (): void {
    Reseller::factory()->count(2)->create();
    Media::factory()->create(['size_bytes' => 1000]);
    Media::factory()->create(['size_bytes' => 500]);

    $fakeStorage = new FakeStorageMetering;
    $this->app->instance(StorageMetering::class, $fakeStorage);

    $snapshot = app(PlatformDashboardService::class)->snapshot();

    expect($snapshot->resellerCount)->toBe(2)
        ->and($snapshot->storageUsedBytes)->toBe(1500)
        // 5 GB (FakeStorageMetering's includedBytes()) times 2 resellers.
        ->and($snapshot->storageIncludedBytes)->toBe(5 * 1024 * 1024 * 1024 * 2)
        ->and($snapshot->billedRevenue->isZero())->toBeTrue()
        ->and($snapshot->pendingRevenue->isZero())->toBeTrue();
});
