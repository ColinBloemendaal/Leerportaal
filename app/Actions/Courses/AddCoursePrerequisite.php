<?php

declare(strict_types=1);

namespace App\Actions\Courses;

use App\Exceptions\CircularCoursePrerequisiteException;
use App\Models\Course;
use Illuminate\Database\ConnectionInterface;

final readonly class AddCoursePrerequisite
{
    public function __construct(private ConnectionInterface $db) {}

    public function __invoke(Course $course, Course $prerequisite): void
    {
        if ($course->is($prerequisite)) {
            throw CircularCoursePrerequisiteException::between($course, $prerequisite);
        }

        if ($this->dependsOn($prerequisite, $course)) {
            throw CircularCoursePrerequisiteException::between($course, $prerequisite);
        }

        $this->db->transaction(function () use ($course, $prerequisite): void {
            $course->prerequisites()->syncWithoutDetaching([$prerequisite->id]);
        });
    }

    /**
     * Whether $course (transitively) requires $target to be completed first.
     */
    private function dependsOn(Course $course, Course $target): bool
    {
        $visited = [];
        $queue = $course->prerequisites()->get();

        while ($queue->isNotEmpty()) {
            /** @var Course $candidate */
            $candidate = $queue->shift();

            if ($candidate->is($target)) {
                return true;
            }

            if (isset($visited[$candidate->id])) {
                continue;
            }

            $visited[$candidate->id] = true;
            $queue = $queue->merge($candidate->prerequisites()->get());
        }

        return false;
    }
}
