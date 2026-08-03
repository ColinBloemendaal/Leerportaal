<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Notifications\SendAssignmentDeadlineReminder;
use App\Contracts\Repositories\CourseAssignmentRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class SendAssignmentDeadlineRemindersCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reports:assignment-deadlines';

    /**
     * @var string
     */
    protected $description = 'Send deadline-approaching and overdue notifications for course assignments, once each';

    public function handle(CourseAssignmentRepository $assignments, SendAssignmentDeadlineReminder $sendReminder): int
    {
        $today = Carbon::today();
        $assignmentCount = 0;
        $sentCount = 0;

        foreach ($assignments->dueForDeadlineEvaluation() as $assignment) {
            $sentCount += $sendReminder($assignment, $today);
            $assignmentCount++;
        }

        $this->info("Evaluated {$assignmentCount} assignment(s), sent {$sentCount} reminder(s).");

        return self::SUCCESS;
    }
}
