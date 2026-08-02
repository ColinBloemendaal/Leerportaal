<?php

declare(strict_types=1);

namespace App\Services\Progress;

use App\Models\Block;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Support\Collection;

/**
 * Pure calculation over block_progress -- lesson/module/course
 * completion is derived here, never stored redundantly (only blocks
 * have a real completed_at row, per the schema task's own reasoning).
 */
final readonly class ProgressCalculationService
{
    public function lessonCompletionPercent(CourseAssignment $assignment, Lesson $lesson): float
    {
        return $this->percentComplete($assignment, $lesson->blocks()->pluck('id'));
    }

    public function moduleCompletionPercent(CourseAssignment $assignment, Module $module): float
    {
        $lessonIds = $module->lessons()->pluck('id');
        $blockIds = Block::query()->whereIn('lesson_id', $lessonIds)->pluck('id');

        return $this->percentComplete($assignment, $blockIds);
    }

    public function courseCompletionPercent(CourseAssignment $assignment, Course $course): float
    {
        $moduleIds = $course->modules()->pluck('id');
        $lessonIds = Lesson::query()->whereIn('module_id', $moduleIds)->pluck('id');
        $blockIds = Block::query()->whereIn('lesson_id', $lessonIds)->pluck('id');

        return $this->percentComplete($assignment, $blockIds);
    }

    /**
     * The block to resume at: the most recently viewed one, or the very
     * first block of the course if nothing has been viewed yet.
     */
    public function resumeBlock(CourseAssignment $assignment, Course $course): ?Block
    {
        // Ties on last_viewed_at (same-second successive views, common
        // in tests and entirely possible in real use) break toward the
        // higher id -- the row written by the later of the two calls.
        $lastViewed = $assignment->blockProgress()
            ->whereNotNull('last_viewed_at')
            ->orderByDesc('last_viewed_at')
            ->orderByDesc('id')
            ->first();

        return $lastViewed !== null ? $lastViewed->block : $this->firstBlockOf($course);
    }

    /**
     * @param  Collection<int, int>  $blockIds
     */
    private function percentComplete(CourseAssignment $assignment, Collection $blockIds): float
    {
        if ($blockIds->isEmpty()) {
            return 0.0;
        }

        $completedCount = $assignment->blockProgress()
            ->whereIn('block_id', $blockIds)
            ->whereNotNull('completed_at')
            ->count();

        return round($completedCount / $blockIds->count() * 100, 2);
    }

    /**
     * Walks module -> lesson -> block in order rather than a flat
     * whereIn+orderBy('order') -- `order` is only unique within its own
     * parent, so a flat query can't correctly compare positions across
     * different lessons/modules.
     */
    private function firstBlockOf(Course $course): ?Block
    {
        $modules = $course->modules()->orderBy('order')->get();

        foreach ($modules as $module) {
            $lessons = $module->lessons()->orderBy('order')->get();

            foreach ($lessons as $lesson) {
                $firstBlock = $lesson->blocks()->orderBy('order')->first();

                if ($firstBlock !== null) {
                    return $firstBlock;
                }
            }
        }

        return null;
    }
}
