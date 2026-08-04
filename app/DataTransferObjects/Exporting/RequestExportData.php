<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Exporting;

use App\Enums\ExportFormat;
use App\Enums\FilterableResource;

final readonly class RequestExportData
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public int $userId,
        public ?int $resellerId,
        public FilterableResource $resourceType,
        public ExportFormat $format,
        public array $filters,
    ) {}
}
