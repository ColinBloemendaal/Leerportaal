<?php

declare(strict_types=1);

namespace App\Actions\Progress;

use App\Models\Block;
use App\Models\BlockProgress;
use App\Models\CourseAssignment;

final readonly class MarkBlockViewed
{
    public function __invoke(CourseAssignment $assignment, Block $block): BlockProgress
    {
        return BlockProgress::query()->updateOrCreate(
            ['course_assignment_id' => $assignment->id, 'block_id' => $block->id],
            ['last_viewed_at' => now()],
        );
    }
}
