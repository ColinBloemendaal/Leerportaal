<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\NotificationType;

interface AssignmentReminderRepository
{
    public function hasBeenSent(int $courseAssignmentId, NotificationType $type, ?int $daysBefore): bool;
}
