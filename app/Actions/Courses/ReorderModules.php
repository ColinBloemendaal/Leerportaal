<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\DataTransferObjects\Courses\ReorderItemsData;
use App\Models\Course;
use App\Services\Ordering\SiblingOrderingService;

final readonly class ReorderModules
{
    public function __construct(private SiblingOrderingService $ordering) {}

    public function __invoke(Course $course, ReorderItemsData $data): void
    {
        $this->ordering->reorder($course->modules(), $data->orderedIds);
    }
}
