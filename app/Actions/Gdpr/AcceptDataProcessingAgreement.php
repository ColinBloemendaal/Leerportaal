<?php

declare(strict_types=1);

namespace App\Actions\Gdpr;

use App\Models\Reseller;
use App\Models\User;

/**
 * CLAUDE.md §8 (GDPR): "Per-reseller DPA acceptance and record." Stamps
 * the exact version being accepted (config('gdpr.dpa_version')), not
 * just "accepted" -- Reseller::hasAcceptedCurrentDpa() compares against
 * that stored version, so a later DPA text change makes this stale
 * again rather than silently staying "accepted" forever.
 */
final readonly class AcceptDataProcessingAgreement
{
    public function __invoke(Reseller $reseller, User $acceptedBy): Reseller
    {
        $reseller->dpa_accepted_at = now()->toImmutable();
        $reseller->dpa_accepted_version = config('gdpr.dpa_version');
        $reseller->dpa_accepted_by_user_id = max(0, $acceptedBy->id);
        $reseller->save();

        return $reseller;
    }
}
