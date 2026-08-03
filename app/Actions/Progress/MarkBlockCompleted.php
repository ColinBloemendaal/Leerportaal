<?php

declare(strict_types=1);

namespace App\Actions\Progress;

use App\Models\Block;
use App\Models\BlockProgress;
use App\Models\CourseAssignment;
use App\Notifications\CourseCompletedNotification;
use App\Services\Progress\CourseCompletionChecker;

/**
 * The only real write path today that can flip a course from incomplete
 * to complete -- CourseCompletionChecker's other two rules (pass_exam,
 * minimum_score) key off QuizAttempt::submitted_at, but no "submit a quiz
 * attempt" Action exists yet anywhere in this codebase to hook a
 * completion check into. Those two rules' Completion notification is a
 * real, known gap until that Action is built, not something faked here.
 */
final readonly class MarkBlockCompleted
{
    public function __construct(private CourseCompletionChecker $completionChecker) {}

    public function __invoke(CourseAssignment $assignment, Block $block): BlockProgress
    {
        $progress = BlockProgress::query()->updateOrCreate(
            ['course_assignment_id' => $assignment->id, 'block_id' => $block->id],
            ['completed_at' => now(), 'last_viewed_at' => now()],
        );

        $this->notifyIfJustCompleted($assignment, $block);

        return $progress;
    }

    private function notifyIfJustCompleted(CourseAssignment $assignment, Block $block): void
    {
        // Already notified once for this assignment -- CourseCompletionChecker
        // itself has no memory of "was this already true before", so this
        // column is what makes the notification fire exactly once.
        if ($assignment->completed_at !== null) {
            return;
        }

        $lesson = $block->lesson()->first();
        $module = $lesson?->module()->first();
        $course = $module?->course()->first();

        if ($course === null || ! $this->completionChecker->isComplete($assignment, $course)) {
            return;
        }

        $assignment->forceFill(['completed_at' => now()])->save();

        $user = $assignment->user()->first();
        $user?->notify(new CourseCompletedNotification($assignment));
    }
}
