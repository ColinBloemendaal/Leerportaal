<?php

declare(strict_types=1);

namespace App\Actions\Gdpr;

use App\Services\Gdpr\RetentionPolicy;
use Spatie\Activitylog\Models\Activity;

/**
 * CLAUDE.md §8 (GDPR): "Retention policies per data type." Platform-wide
 * only, not reseller-configurable -- see RetentionPolicy::activityLogDays()'s
 * own reasoning: this is the audit trail CLAUDE.md §7 requires be
 * preserved, and letting a reseller shorten it would defeat the point.
 */
final readonly class PurgeOldActivityLogEntries
{
    public function __construct(private RetentionPolicy $retention) {}

    public function __invoke(): int
    {
        $cutoff = now()->subDays($this->retention->activityLogDays());

        return Activity::query()->where('created_at', '<', $cutoff)->delete();
    }
}
