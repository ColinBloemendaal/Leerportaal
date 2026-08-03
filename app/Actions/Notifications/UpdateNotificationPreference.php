<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;

final readonly class UpdateNotificationPreference
{
    public function __invoke(int $userId, NotificationType $type, NotificationChannel $channel, bool $enabled): NotificationPreference
    {
        return NotificationPreference::query()->updateOrCreate(
            ['user_id' => $userId, 'type' => $type, 'channel' => $channel],
            ['enabled' => $enabled],
        );
    }
}
