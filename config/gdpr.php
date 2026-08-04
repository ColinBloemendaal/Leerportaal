<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Retention defaults
    |--------------------------------------------------------------------------
    |
    | CLAUDE.md §8 (GDPR): "Retention policies per data type, enforced by
    | scheduled job, configurable per reseller." These are the platform-wide
    | defaults; a reseller may shorten (not lengthen -- see RetentionPolicy)
    | notifications_days and expired_exports_grace_days via its own
    | `settings->retention` JSON. activity_log_days is deliberately NOT
    | reseller-configurable: it's the audit trail CLAUDE.md §7 requires be
    | preserved, and letting a reseller shorten their own audit retention
    | would defeat the point of it being an audit trail at all.
    |
    */

    'retention' => [
        'activity_log_days' => (int) env('RETENTION_ACTIVITY_LOG_DAYS', 730),
        'notifications_days' => (int) env('RETENTION_NOTIFICATIONS_DAYS', 180),
        'expired_exports_grace_days' => (int) env('RETENTION_EXPIRED_EXPORTS_GRACE_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Processing Agreement version
    |--------------------------------------------------------------------------
    |
    | CLAUDE.md §7/§8: "Every reseller gets a DPA" / "Per-reseller DPA
    | acceptance and record." Bump this (and docs/dpa.md's own version
    | header) whenever the DPA text changes -- a reseller's stored
    | `dpa_accepted_version` is compared against this exact string, so
    | changing it is what makes every reseller's existing acceptance
    | stale and prompts them to review and re-accept.
    |
    */

    'dpa_version' => env('DPA_VERSION', '2026-08-04'),

];
