<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\AssignmentReminderRepository;
use App\Enums\NotificationType;
use App\Models\AssignmentReminder;

final class EloquentAssignmentReminderRepository implements AssignmentReminderRepository
{
    public function hasBeenSent(int $courseAssignmentId, NotificationType $type, ?int $daysBefore): bool
    {
        return AssignmentReminder::query()
            ->where('course_assignment_id', $courseAssignmentId)
            ->where('type', $type)
            ->where('days_before', $daysBefore)
            ->exists();
    }
}
