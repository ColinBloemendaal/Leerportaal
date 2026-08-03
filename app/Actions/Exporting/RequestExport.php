<?php

declare(strict_types=1);

namespace App\Actions\Exporting;

use App\DataTransferObjects\Exporting\RequestExportData;
use App\Enums\ExportStatus;
use App\Jobs\GenerateExportJob;
use App\Models\Export;

final readonly class RequestExport
{
    public function __invoke(RequestExportData $data): Export
    {
        $export = Export::query()->create([
            'user_id' => $data->userId,
            'reseller_id' => $data->resellerId,
            'resource_type' => $data->resourceType,
            'filters' => $data->filters,
            // Explicit rather than relying on the migration's DB-level
            // default: Eloquent doesn't refetch column defaults onto the
            // in-memory model after create(), so $export->status would
            // otherwise be null until the model was re-fetched.
            'status' => ExportStatus::Pending,
        ]);

        GenerateExportJob::dispatch($export->id);

        return $export;
    }
}
