<?php

declare(strict_types=1);

use App\Models\FailedJob;
use App\Models\Media;
use App\Services\Reporting\PlatformHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Horizon\Contracts\WorkloadRepository;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('composes queue workload, failed job counts, and storage usage into one snapshot', function (): void {
    $fakeWorkload = new class implements WorkloadRepository
    {
        public function get()
        {
            return [
                ['name' => 'default', 'length' => 3, 'wait' => 5, 'processes' => 2, 'split_queues' => null],
            ];
        }
    };
    $this->app->instance(WorkloadRepository::class, $fakeWorkload);

    FailedJob::factory()->create(['failed_at' => now()->subHours(1)]);
    FailedJob::factory()->create(['failed_at' => now()->subDays(3)]);
    Media::factory()->create(['size_bytes' => 1234]);

    $snapshot = app(PlatformHealthService::class)->snapshot();

    expect($snapshot->queues)->toHaveCount(1)
        ->and($snapshot->queues[0]->name)->toBe('default')
        ->and($snapshot->queues[0]->length)->toBe(3)
        ->and($snapshot->failedJobCount)->toBe(2)
        ->and($snapshot->failedJobCountLast24Hours)->toBe(1)
        ->and($snapshot->storageUsedBytes)->toBe(1234);
});
