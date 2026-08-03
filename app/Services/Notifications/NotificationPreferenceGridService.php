<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Contracts\Repositories\NotificationPreferenceRepository;
use App\DataTransferObjects\Notifications\NotificationPreferenceRowData;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use Illuminate\Database\Eloquent\Collection;

/**
 * Combines the fixed catalogue of preference-eligible types with a
 * user's sparse stored overrides into the full type x channel grid the
 * settings page renders -- deliberately a Service, not a Repository
 * method (this isn't a query, it's pure calculation over data the
 * repository already returns) or an Http\Resources class (there's no
 * single model/collection this shapes, it's a fixed enum list merged
 * with sparse override rows).
 */
final readonly class NotificationPreferenceGridService
{
    /**
     * Password and Invite have no preference-eligible Notification class
     * (Laravel's own built-in reset notification, and a not-yet-a-User
     * recipient, respectively); Billing has no notification built yet;
     * AdminAlert is deliberately excluded -- see
     * App\Notifications\Concerns\RespectsPreferences's own docblock.
     *
     * @var list<NotificationType>
     */
    private const ELIGIBLE_TYPES = [
        NotificationType::Welcome,
        NotificationType::Assignment,
        NotificationType::Deadline,
        NotificationType::Overdue,
        NotificationType::Completion,
        NotificationType::Certificate,
    ];

    public function __construct(private NotificationPreferenceRepository $preferences) {}

    /**
     * @return list<NotificationPreferenceRowData>
     */
    public function gridFor(int $userId): array
    {
        $overrides = $this->preferences->forUser($userId);

        return array_map(
            fn (NotificationType $type): NotificationPreferenceRowData => new NotificationPreferenceRowData(
                type: $type->value,
                label: $type->label(),
                channels: $this->channelsFor($type, $overrides),
            ),
            self::ELIGIBLE_TYPES,
        );
    }

    /**
     * @param  Collection<int, NotificationPreference>  $overrides
     * @return array<string, bool>
     */
    private function channelsFor(NotificationType $type, Collection $overrides): array
    {
        $channels = [];

        foreach (NotificationChannel::cases() as $channel) {
            $override = $overrides->first(
                fn (NotificationPreference $preference): bool => $preference->type === $type && $preference->channel === $channel,
            );
            $channels[$channel->value] = $override === null ? true : $override->enabled;
        }

        return $channels;
    }
}
