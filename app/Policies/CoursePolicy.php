<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

/**
 * Placeholder until roles exist (Phase 1 task 34): any authenticated user
 * can view a course they can already see (visibility itself is
 * EloquentCourseRepository's job, not this policy's). The one real rule
 * enforced here is CLAUDE.md §1: catalog (platform-owned, reseller_id
 * null) courses are read-only for resellers -- no forking, no
 * copy-on-write. Only platform staff (reseller_id null) may write to a
 * catalog course; a reseller may only write to their own course. Super
 * admin reaches every action via Gate::before regardless.
 *
 * No course-creation controller/Action exists anywhere in the codebase
 * yet (Phase 3/4's own course-authoring UI is still unbuilt) -- create()
 * is nonetheless the real, complete enforcement point CLAUDE.md §11's
 * "authoring add-on unlocks custom course creation" needs, and will be
 * exercised the moment that controller exists; there's nothing left to
 * "wire up" once it does.
 */
final class CoursePolicy
{
    public function view(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Platform staff author catalog courses -- no reseller
        // subscription concept applies to them.
        if ($user->reseller_id === null) {
            return true;
        }

        $reseller = $user->reseller()->first();

        return $reseller !== null && $reseller->hasActiveAuthoringAddon();
    }

    public function update(User $user, Course $course): bool
    {
        return $this->canWrite($user, $course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->canWrite($user, $course);
    }

    public function restore(User $user, Course $course): bool
    {
        return $this->canWrite($user, $course);
    }

    private function canWrite(User $user, Course $course): bool
    {
        if ($course->reseller_id === null) {
            return $user->reseller_id === null;
        }

        return $user->reseller_id === $course->reseller_id;
    }
}
