<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FailedJob;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FailedJob>
 */
final class FailedJobFactory extends Factory
{
    protected $model = FailedJob::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'connection' => 'redis',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Jobs\\ExampleJob']),
            'exception' => 'RuntimeException: something went wrong',
            'failed_at' => now(),
        ];
    }
}
