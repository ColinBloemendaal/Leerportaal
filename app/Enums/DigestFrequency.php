<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * A single, account-wide email delivery cadence -- not per notification
 * type like NotificationChannel's own preferences (a "digest" concept
 * only applies to the mail channel; the in-app database channel is
 * unaffected and always immediate, since there's no reason to batch
 * something the user already sees live in the notification centre).
 */
enum DigestFrequency: string
{
    case Immediate = 'immediate';
    case Daily = 'daily';
    case Weekly = 'weekly';

    public function label(): string
    {
        return match ($this) {
            self::Immediate => 'Immediately',
            self::Daily => 'Daily digest',
            self::Weekly => 'Weekly digest',
        };
    }
}
