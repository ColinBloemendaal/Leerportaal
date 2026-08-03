<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Enums\DigestFrequency;
use App\Models\User;

final readonly class UpdateNotificationDigestFrequency
{
    public function __invoke(int $userId, DigestFrequency $frequency): void
    {
        User::query()->whereKey($userId)->update(['notification_digest_frequency' => $frequency]);
    }
}
