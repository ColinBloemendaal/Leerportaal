<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Notifications;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;

final readonly class UpdateNotificationPreferenceData
{
    public function __construct(
        public NotificationType $type,
        public NotificationChannel $channel,
        public bool $enabled,
    ) {}
}
