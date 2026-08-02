<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\DataTransferObjects\Courses\ReorderItemsData;
use App\Models\Lesson;
use App\Services\Ordering\SiblingOrderingService;

final readonly class ReorderBlocks
{
    public function __construct(private SiblingOrderingService $ordering) {}

    public function __invoke(Lesson $lesson, ReorderItemsData $data): void
    {
        $this->ordering->reorder($lesson->blocks(), $data->orderedIds);
    }
}
