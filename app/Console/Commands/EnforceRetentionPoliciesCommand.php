<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Gdpr\PurgeExpiredExports;
use App\Actions\Gdpr\PurgeOldActivityLogEntries;
use App\Actions\Gdpr\PurgeStaleNotifications;
use App\Contracts\Repositories\ResellerRepository;
use Illuminate\Console\Command;

/**
 * CLAUDE.md §8 (GDPR): "Retention policies per data type, enforced by
 * scheduled job, configurable per reseller." Notifications and exports
 * are purged once per reseller (their own retention override, if any,
 * applies) plus once more for platform-context rows (reseller_id null);
 * the activity log is purged exactly once, platform-wide, since it's
 * deliberately not reseller-configurable.
 */
final class EnforceRetentionPoliciesCommand extends Command
{
    protected $signature = 'gdpr:enforce-retention';

    protected $description = 'Purge notifications, expired exports, and activity log entries past their retention window';

    public function handle(
        ResellerRepository $resellers,
        PurgeStaleNotifications $purgeNotifications,
        PurgeExpiredExports $purgeExports,
        PurgeOldActivityLogEntries $purgeActivityLog,
    ): int {
        $notificationsDeleted = $purgeNotifications(null);
        $exportsDeleted = $purgeExports(null);

        foreach ($resellers->all() as $reseller) {
            $notificationsDeleted += $purgeNotifications($reseller);
            $exportsDeleted += $purgeExports($reseller);
        }

        $activityLogDeleted = $purgeActivityLog();

        $this->info("Purged {$notificationsDeleted} notifications, {$exportsDeleted} expired exports, {$activityLogDeleted} activity log entries.");

        return self::SUCCESS;
    }
}
