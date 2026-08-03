<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\ExportStatus;
use App\Enums\FilterableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * @property int $id
 * @property FilterableResource $resource_type
 * @property ExportStatus $status
 * @property ?string $failure_reason
 * @property ?Carbon $expires_at
 * @property Carbon $created_at
 */
final class ExportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'resource_type' => $this->resource_type->value,
            'status' => $this->status->value,
            'failure_reason' => $this->failure_reason,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            // A fresh signed URL every time this resource is serialized,
            // valid until the export itself expires -- never persisted,
            // so it can't be replayed past that window even if cached
            // somewhere upstream.
            'download_url' => $this->resource->isDownloadable() && $this->expires_at !== null
                ? URL::temporarySignedRoute('admin.exports.download', $this->expires_at, ['export' => $this->id])
                : null,
        ];
    }
}
