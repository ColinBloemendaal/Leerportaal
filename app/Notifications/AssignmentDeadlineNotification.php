<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Mail\AssignmentDeadlineApproaching;
use App\Models\CourseAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class AssignmentDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly CourseAssignment $assignment,
        private readonly int $daysBefore,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): AssignmentDeadlineApproaching
    {
        return (new AssignmentDeadlineApproaching($this->assignment, $this->daysBefore))->to($notifiable->email);
    }

    /**
     * @return array{type: string, course_assignment_id: int, days_before: int}
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'type' => NotificationType::Deadline->value,
            'course_assignment_id' => $this->assignment->id,
            'days_before' => $this->daysBefore,
        ];
    }
}
