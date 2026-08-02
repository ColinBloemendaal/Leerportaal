<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\Enums\CourseStatus;
use App\Exceptions\IncompleteCourseTranslationsException;
use App\Exceptions\InvalidCourseStatusTransitionException;
use App\Models\Course;
use App\Services\Translation\TranslationCompletenessService;

/**
 * "Versioning" here is deliberately minimal -- an incrementing counter
 * and a timestamp, not a content snapshot. See the migration that added
 * these columns for why.
 */
final readonly class PublishCourse
{
    public function __construct(private TranslationCompletenessService $completeness) {}

    public function __invoke(Course $course): Course
    {
        if ($course->status !== CourseStatus::InReview) {
            throw InvalidCourseStatusTransitionException::from($course->status, CourseStatus::Published);
        }

        $incompleteLocales = array_values(array_filter(
            $course->available_locales,
            fn (string $locale): bool => ! $this->completeness->isComplete($course, $locale),
        ));

        if ($incompleteLocales !== []) {
            throw IncompleteCourseTranslationsException::forLocales($incompleteLocales);
        }

        $course->update([
            'status' => CourseStatus::Published,
            'version' => $course->version + 1,
            'published_at' => now(),
        ]);

        return $course;
    }
}
