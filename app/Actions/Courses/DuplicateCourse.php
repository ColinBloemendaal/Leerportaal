<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Models\Course;
use Illuminate\Database\ConnectionInterface;

/**
 * "Within a reseller, and platform-internal" per CLAUDE.md §1 -- catalog
 * courses are read-only for resellers with no forking, so duplication
 * never crosses the platform/reseller ownership boundary. Achieved
 * simply: replicate() carries reseller_id over unchanged, and nothing
 * here ever overrides it.
 *
 * A duplicate is a fresh Draft, not a repeat/variant (variant_year,
 * repeats_from_course_id) -- those are a different, billing-relevant
 * concept per CLAUDE.md §11 and are deliberately not set here.
 */
final readonly class DuplicateCourse
{
    public function __construct(private ConnectionInterface $db) {}

    public function __invoke(Course $course): Course
    {
        return $this->db->transaction(function () use ($course): Course {
            $copy = $course->replicate(['slug', 'status', 'version', 'published_at', 'variant_year', 'repeats_from_course_id']);
            $copy->slug = $this->uniqueSlug($course->slug);
            $copy->status = CourseStatus::Draft;
            $copy->version = 0;
            $copy->published_at = null;
            $copy->variant_year = null;
            $copy->repeats_from_course_id = null;
            $copy->save();

            foreach ($course->getTranslations('title') as $locale => $value) {
                $copy->setTranslation('title', $locale, trans(':title (Copy)', ['title' => $value]));
            }
            $copy->save();

            foreach ($course->modules()->orderBy('order')->get() as $module) {
                $moduleCopy = $module->replicate(['course_id']);
                $moduleCopy->course()->associate($copy);
                $moduleCopy->save();

                foreach ($module->lessons()->orderBy('order')->get() as $lesson) {
                    $lessonCopy = $lesson->replicate(['module_id']);
                    $lessonCopy->module()->associate($moduleCopy);
                    $lessonCopy->save();

                    foreach ($lesson->blocks()->orderBy('order')->get() as $block) {
                        $blockCopy = $block->replicate(['lesson_id']);
                        $blockCopy->lesson()->associate($lessonCopy);
                        $blockCopy->save();
                    }
                }
            }

            return $copy;
        });
    }

    private function uniqueSlug(string $baseSlug): string
    {
        $slug = "{$baseSlug}-copy";
        $suffix = 1;

        // withTrashed(): the slug column's unique index isn't scoped to
        // exclude soft-deleted rows, so a trashed course can still block
        // an insert even though the default query hides it.
        while (Course::withTrashed()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = "{$baseSlug}-copy-{$suffix}";
        }

        return $slug;
    }
}
