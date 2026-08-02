<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Courses;

final readonly class ReorderItemsData
{
    /**
     * @param list<int> $orderedIds
     */
    public function __construct(
        public array $orderedIds,
    ) {}
}
