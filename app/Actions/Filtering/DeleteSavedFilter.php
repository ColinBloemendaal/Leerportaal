<?php

declare(strict_types=1);

namespace App\Actions\Filtering;

use App\Models\SavedFilter;

final readonly class DeleteSavedFilter
{
    public function __invoke(SavedFilter $savedFilter): void
    {
        $savedFilter->delete();
    }
}
