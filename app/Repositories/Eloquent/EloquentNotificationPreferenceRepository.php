<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\NotificationPreferenceRepository;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use Illuminate\Database\Eloquent\Collection;

final class EloquentNotificationPreferenceRepository implements NotificationPreferenceRepository
{
    public function isEnabled(int $userId, NotificationType $type, NotificationChannel $channel): bool
    {
        $preference = NotificationPreference::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('channel', $channel)
            ->first();

        return $preference === null ? true : $preference->enabled;
    }

    public function forUser(int $userId): Collection
    {
        return NotificationPreference::query()->where('user_id', $userId)->get();
    }
}
