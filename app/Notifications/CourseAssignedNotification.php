<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Mail\CourseAssigned;
use App\Models\CourseAssignment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class CourseAssignedNotification extends Notification implements ShouldQueue
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

    public function toMail(User $notifiable): CourseAssigned
    {
        return (new CourseAssigned($this->assignment))->to($notifiable->email);
    }

    /**
     * @return array{type: string, course_assignment_id: int, course_id: int|null}
     */
    public function toDatabase(User $notifiable): array
    {
        return [
            'type' => NotificationType::Assignment->value,
            'course_assignment_id' => $this->assignment->id,
            'course_id' => $this->assignment->course_id,
        ];
    }
}
