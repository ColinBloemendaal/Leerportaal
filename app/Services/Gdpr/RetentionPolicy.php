<?php

declare(strict_types=1);

namespace App\Services\Gdpr;

use App\Models\Reseller;

/**
 * CLAUDE.md §8 (GDPR): "Retention policies per data type, enforced by
 * scheduled job, configurable per reseller." Resolves the effective
 * retention window for a data type: a reseller's own `settings->retention`
 * override if it's a valid, *shorter* number of days, otherwise the
 * platform default from config/gdpr.php. A reseller may only shorten its
 * own retention, never lengthen it past the platform default -- keeping
 * data longer than the platform's own compliance posture allows isn't a
 * reseller's call to make.
 */
final readonly class RetentionPolicy
{
    public function activityLogDays(): int
    {
        // Deliberately not reseller-configurable -- see config/gdpr.php's
        // own comment: this is the audit trail, and a reseller shortening
        // it would defeat the point.
        return $this->platformDefault('activity_log_days');
    }

    public function notificationDaysFor(?Reseller $reseller): int
    {
        return $this->resolve($reseller, 'notifications_days');
    }

    public function expiredExportsGraceDaysFor(?Reseller $reseller): int
    {
        return $this->resolve($reseller, 'expired_exports_grace_days');
    }

    private function resolve(?Reseller $reseller, string $key): int
    {
        $default = $this->platformDefault($key);
        $override = $reseller?->settings['retention'][$key] ?? null;

        if (! is_int($override) || $override <= 0 || $override >= $default) {
            return $default;
        }

        return $override;
    }

    private function platformDefault(string $key): int
    {
        /** @var int $default */
        $default = config("gdpr.retention.{$key}");

        return $default;
    }
}
