<?php

declare(strict_types=1);

namespace App\Notifications\Concerns;

use App\Contracts\Repositories\NotificationPreferenceRepository;
use App\Enums\DigestFrequency;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\User;

/**
 * Shared by every preference-eligible notification type's via() -- not
 * used by AdminAlertNotification (a platform health alert shouldn't be
 * silenceable by an individual super-admin's own preference) or the
 * Password/Invite catalogue entries (Laravel's own built-in notification
 * and a not-yet-a-User recipient respectively, neither of which this
 * trait's User-keyed lookup could apply to anyway).
 */
trait RespectsPreferences
{
    /**
     * @param  list<string>  $defaultChannels
     * @return list<string>
     */
    protected function filterByPreference(object $notifiable, NotificationType $type, array $defaultChannels): array
    {
        if (! $notifiable instanceof User) {
            return $defaultChannels;
        }

        $preferences = app(NotificationPreferenceRepository::class);

        $channels = array_values(array_filter(
            $defaultChannels,
            fn (string $channel): bool => $preferences->isEnabled($notifiable->id, $type, NotificationChannel::from($channel)),
        ));

        // Deferred to a digest, not skipped: the database channel (if
        // still present above) already stores the row a scheduled digest
        // command later reads from -- see SendNotificationDigest. Only
        // the immediate mail send is what a non-immediate frequency
        // suppresses here.
        if ($notifiable->notification_digest_frequency !== DigestFrequency::Immediate) {
            $channels = array_values(array_filter($channels, fn (string $channel): bool => $channel !== 'mail'));
        }

        return $channels;
    }
}
