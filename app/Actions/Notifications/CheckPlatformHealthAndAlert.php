<?php

declare(strict_types=1);

namespace App\Actions\Notifications;

use App\Contracts\Repositories\UserRepository;
use App\Notifications\AdminAlertNotification;
use App\Services\Reporting\PlatformHealthService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Threshold check + alert, not a general-purpose health service method --
 * PlatformHealthService (Phase 7) stays pure "what is the current
 * snapshot", this Action is what decides "is that snapshot bad enough to
 * page someone" and actually sends. Cooldown is a plain cache TTL, not a
 * table: this is a "don't spam every hourly run while the condition
 * persists" debounce, not a compliance record that needs to survive a
 * cache flush.
 */
final readonly class CheckPlatformHealthAndAlert
{
    private const FAILED_JOBS_THRESHOLD = 10;

    private const COOLDOWN_MINUTES = 24 * 60;

    private const CACHE_KEY = 'admin-alert:failed-jobs-spike';

    public function __construct(
        private PlatformHealthService $health,
        private UserRepository $users,
        private CacheRepository $cache,
    ) {}

    /**
     * Returns true if an alert was actually sent.
     */
    public function __invoke(): bool
    {
        $snapshot = $this->health->snapshot();

        if ($snapshot->failedJobCountLast24Hours <= self::FAILED_JOBS_THRESHOLD) {
            return false;
        }

        if ($this->cache->has(self::CACHE_KEY)) {
            return false;
        }

        $message = trans(
            ':count failed jobs in the last 24 hours, above the alert threshold of :threshold.',
            ['count' => $snapshot->failedJobCountLast24Hours, 'threshold' => self::FAILED_JOBS_THRESHOLD],
        );

        foreach ($this->users->superAdmins() as $admin) {
            $admin->notify(new AdminAlertNotification(trans('Platform health alert'), $message));
        }

        $this->cache->put(self::CACHE_KEY, true, now()->addMinutes(self::COOLDOWN_MINUTES));

        return true;
    }
}
