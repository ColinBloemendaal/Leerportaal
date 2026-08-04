<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use App\Enums\FilterableResource;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Export>
 */
final class ExportFactory extends Factory
{
    protected $model = Export::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'reseller_id' => null,
            'resource_type' => FilterableResource::Resellers,
            'format' => ExportFormat::Csv,
            'filters' => [],
            'status' => ExportStatus::Pending,
            'disk' => null,
            'path' => null,
            'failure_reason' => null,
            'expires_at' => null,
        ];
    }

    public function completed(): self
    {
        return $this->state(fn (): array => [
            'status' => ExportStatus::Completed,
            'disk' => 'local',
            'path' => 'exports/'.fake()->uuid().'.csv',
            'expires_at' => now()->addDay(),
        ]);
    }

    public function xlsx(): self
    {
        return $this->state(fn (): array => ['format' => ExportFormat::Xlsx]);
    }

    public function failed(string $reason = 'Something went wrong.'): self
    {
        return $this->state(fn (): array => [
            'status' => ExportStatus::Failed,
            'failure_reason' => $reason,
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (): array => [
            'status' => ExportStatus::Completed,
            'disk' => 'local',
            'path' => 'exports/'.fake()->uuid().'.csv',
            'expires_at' => now()->subDay(),
        ]);
    }
}
