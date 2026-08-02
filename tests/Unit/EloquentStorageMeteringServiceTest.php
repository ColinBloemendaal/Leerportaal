<?php

declare(strict_types=1);

use App\Contracts\Repositories\MediaRepository;
use App\Models\Media;
use App\Models\Reseller;
use App\Services\Storage\EloquentStorageMeteringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = new EloquentStorageMeteringService(app(MediaRepository::class));
});

it('reports 5 GB as the included allowance', function (): void {
    expect($this->service->includedBytes())->toBe(5 * 1024 * 1024 * 1024);
});

it('sums only that reseller\'s own media, never platform media', function (): void {
    $reseller = Reseller::factory()->create();
    Media::factory()->forReseller($reseller->id)->create(['size_bytes' => 1000]);
    Media::factory()->forReseller($reseller->id)->create(['size_bytes' => 2000]);
    Media::factory()->create(['size_bytes' => 999_999]); // platform media

    expect($this->service->usageBytes($reseller->id))->toBe(3000);
});

it('is not over limit when usage is under the included allowance', function (): void {
    $reseller = Reseller::factory()->create();
    Media::factory()->forReseller($reseller->id)->create(['size_bytes' => 1024]);

    expect($this->service->isOverLimit($reseller->id))->toBeFalse();
});

it('is over limit when usage exceeds the included allowance', function (): void {
    $reseller = Reseller::factory()->create();
    Media::factory()->forReseller($reseller->id)->create(['size_bytes' => 6 * 1024 * 1024 * 1024]);

    expect($this->service->isOverLimit($reseller->id))->toBeTrue();
});

it('reports zero usage for a reseller with no media', function (): void {
    $reseller = Reseller::factory()->create();

    expect($this->service->usageBytes($reseller->id))->toBe(0);
});
