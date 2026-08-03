<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Contracts\Repositories\FailedJobRepository;
use App\Contracts\Repositories\MediaRepository;
use App\DataTransferObjects\Reporting\PlatformHealthData;
use App\DataTransferObjects\Reporting\QueueWorkloadData;
use Laravel\Horizon\Contracts\WorkloadRepository;

/**
 * "Error rate" (TODO.md's own task wording) is approximated as failed
 * queue jobs in the last 24 hours -- Sentry is currently write-only in
 * this codebase (App\Support\SentryPiiRedactor only shapes what gets
 * reported *to* it), with no API client configured to query error
 * counts back out. This is the closest real, locally-available signal
 * rather than a guess at a Sentry integration that doesn't exist yet.
 */
final readonly class PlatformHealthService
{
    public function __construct(
        private WorkloadRepository $workload,
        private FailedJobRepository $failedJobs,
        private MediaRepository $media,
    ) {}

    public function snapshot(): PlatformHealthData
    {
        $queues = array_values(array_map(
            fn (array $queue): QueueWorkloadData => new QueueWorkloadData(
                name: $queue['name'],
                length: $queue['length'],
                waitSeconds: $queue['wait'],
                processes: $queue['processes'],
            ),
            $this->workload->get(),
        ));

        return new PlatformHealthData(
            queues: $queues,
            failedJobCount: $this->failedJobs->count(),
            failedJobCountLast24Hours: $this->failedJobs->countSince(now()->subDay()),
            storageUsedBytes: $this->media->totalBytesAcrossPlatform(),
        );
    }
}
