<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The two channels every preference-eligible notification type in this
 * codebase actually uses -- matches the literal ['mail', 'database']
 * every App\Notifications\* class's via() returns before preference
 * filtering is applied.
 */
enum NotificationChannel: string
{
    case Mail = 'mail';
    case Database = 'database';

    public function label(): string
    {
        return match ($this) {
            self::Mail => 'Email',
            self::Database => 'In-app',
        };
    }
}
