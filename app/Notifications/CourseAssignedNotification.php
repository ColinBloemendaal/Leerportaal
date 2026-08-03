<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Mail\CourseAssigned;
use App\Models\CourseAssignment;
use App\Models\User;
use App\Notifications\Concerns\RespectsPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class CourseAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use RespectsPreferences;

    public function __construct(private readonly CourseAssignment $assignment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $this->filterByPreference($notifiable, NotificationType::Assignment, ['mail', 'database']);
    }

    public function toMail(User $notifiable): CourseAssigned
    {
        return (new CourseAssigned($this->assignment))->to($notifiable->email);
    }

    /**
     * @return array{type: string, message: string, course_assignment_id: int, course_id: int|null}
     */
    public function toDatabase(User $notifiable): array
    {
        // Queried explicitly, not $this->assignment->course: rendering a
        // human-readable line for the in-app notification centre needs the
        // title now, at send time, not a lazy-loaded property that may
        // never have been populated on this instance.
        $course = $this->assignment->course()->first();

        return [
            'type' => NotificationType::Assignment->value,
            'message' => trans('You have been assigned :course', [
                'course' => $course === null ? trans('a new course') : $course->title,
            ]),
            'course_assignment_id' => $this->assignment->id,
            'course_id' => $this->assignment->course_id,
        ];
    }
}
