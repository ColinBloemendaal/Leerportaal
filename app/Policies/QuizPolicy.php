<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CourseAssignment;
use App\Models\Quiz;
use App\Models\User;

/**
 * A cursist may take a quiz only if they hold a live (non-revoked)
 * assignment for the course the quiz actually belongs to -- resolved via
 * Quiz::resolveCourse() since a quiz has no direct course_id of its own.
 */
final class QuizPolicy
{
    public function start(User $user, Quiz $quiz): bool
    {
        $course = $quiz->resolveCourse();

        if ($course === null) {
            return false;
        }

        // withoutTenantScope(): already fully pinned by user_id, so the
        // tenant scope adds nothing -- it would only force every caller
        // (a policy check has no HTTP request of its own to resolve an
        // ambient tenant from) to first resolve TenantContext just to
        // run an already-unambiguous, user-scoped read.
        return CourseAssignment::query()
            ->withoutTenantScope()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereNull('revoked_at')
            ->exists();
    }
}
