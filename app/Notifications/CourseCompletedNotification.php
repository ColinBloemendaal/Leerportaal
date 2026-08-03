<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Mail\CourseCompleted;
use App\Models\CourseAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class CourseCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly CourseAssignment $assignment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): CourseCompleted
    {
        return (new CourseCompleted($this->assignment))->to($notifiable->email);
    }

    /**
     * @return array{type: string, course_assignment_id: int}
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'type' => NotificationType::Completion->value,
            'course_assignment_id' => $this->assignment->id,
        ];
    }
}
