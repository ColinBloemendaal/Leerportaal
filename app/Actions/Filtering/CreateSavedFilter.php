<?php

declare(strict_types=1);

namespace App\Actions\Filtering;

use App\DataTransferObjects\Filtering\CreateSavedFilterData;
use App\Models\SavedFilter;

final readonly class CreateSavedFilter
{
    public function __invoke(CreateSavedFilterData $data): SavedFilter
    {
        return SavedFilter::query()->create([
            'user_id' => $data->userId,
            'resource_type' => $data->resourceType,
            'name' => $data->name,
            'filters' => $data->filters,
        ]);
    }
}
