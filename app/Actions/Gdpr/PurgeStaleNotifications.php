<?php

declare(strict_types=1);

namespace App\Actions\Gdpr;

use App\Models\Reseller;
use App\Models\User;
use App\Services\Gdpr\RetentionPolicy;
use Illuminate\Notifications\DatabaseNotification;

/**
 * CLAUDE.md §8 (GDPR): "Retention policies per data type." Deletes
 * notifications older than the effective retention window for one
 * reseller's users (or, when $reseller is null, platform staff -- users
 * with no reseller_id at all).
 */
final readonly class PurgeStaleNotifications
{
    public function __construct(private RetentionPolicy $retention) {}

    public function __invoke(?Reseller $reseller): int
    {
        $cutoff = now()->subDays($this->retention->notificationDaysFor($reseller));

        $userIds = User::query()
            ->where('reseller_id', $reseller?->id)
            ->pluck('id');

        return DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->whereIn('notifiable_id', $userIds)
            ->where('created_at', '<', $cutoff)
            ->delete();
    }
}
