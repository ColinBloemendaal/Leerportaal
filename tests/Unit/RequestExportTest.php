<?php

declare(strict_types=1);

use App\Actions\Exporting\RequestExport;
use App\DataTransferObjects\Exporting\RequestExportData;
use App\Enums\ExportStatus;
use App\Enums\FilterableResource;
use App\Jobs\GenerateExportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a pending export and dispatches the generation job', function (): void {
    Queue::fake();

    $user = User::factory()->create();

    $export = app(RequestExport::class)(new RequestExportData(
        userId: $user->id,
        resellerId: null,
        resourceType: FilterableResource::Resellers,
        filters: ['search' => 'acme'],
    ));

    expect($export->user_id)->toBe($user->id)
        ->and($export->resource_type)->toBe(FilterableResource::Resellers)
        ->and($export->status)->toBe(ExportStatus::Pending)
        ->and($export->filters)->toBe(['search' => 'acme']);

    Queue::assertPushed(GenerateExportJob::class, fn (GenerateExportJob $job): bool => $job->exportId === $export->id);
});
