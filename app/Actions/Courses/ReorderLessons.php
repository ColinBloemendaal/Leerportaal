<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\DataTransferObjects\Courses\ReorderItemsData;
use App\Models\Module;
use App\Services\Ordering\SiblingOrderingService;

final readonly class ReorderLessons
{
    public function __construct(private SiblingOrderingService $ordering) {}

    public function __invoke(Module $module, ReorderItemsData $data): void
    {
        $this->ordering->reorder($module->lessons(), $data->orderedIds);
    }
}
