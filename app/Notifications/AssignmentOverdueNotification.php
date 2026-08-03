<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Mail\AssignmentOverdue;
use App\Models\CourseAssignment;
use App\Models\User;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class AssignmentOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RespectsPreferences;

    public function __construct(private readonly CourseAssignment $assignment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $this->filterByPreference($notifiable, NotificationType::Overdue, ['mail', 'database']);
    }

    public function toMail(User $notifiable): AssignmentOverdue
    {
        return (new AssignmentOverdue($this->assignment))->to($notifiable->email);
    }

    /**
     * @return array{type: string, message: string, course_assignment_id: int}
     */
    public function toDatabase(User $notifiable): array
    {
        $course = $this->assignment->course()->first();

        return [
            'type' => NotificationType::Overdue->value,
            'message' => trans(':course is overdue', [
                'course' => $course === null ? trans('Your course') : $course->title,
            ]),
            'course_assignment_id' => $this->assignment->id,
        ];
    }
}
