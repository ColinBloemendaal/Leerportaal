<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use Illuminate\Database\Eloquent\Collection;

interface NotificationPreferenceRepository
{
    /**
     * Defaults to true (opt-out model): a preference only exists once a
     * user has explicitly touched its toggle.
     */
    public function isEnabled(int $userId, NotificationType $type, NotificationChannel $channel): bool;

    /**
     * Every explicit override this user has saved -- for the settings
     * page, which merges this against the full type/channel grid to know
     * which cells are actually toggled off.
     *
     * @return Collection<int, NotificationPreference>
     */
    public function forUser(int $userId): Collection;
}
