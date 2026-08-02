<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Filtering;

use App\Enums\FilterableResource;

final readonly class CreateSavedFilterData
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public int $userId,
        public FilterableResource $resourceType,
        public string $name,
        public array $filters,
    ) {}
}
