<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The fixed catalogue of notification categories in the system (TODO.md
 * Phase 8's own list). Each case will eventually back one App\Notifications
 * class carrying both a mail and a database channel -- built in the next
 * Phase 8 task -- and later a per-user/per-channel preference row keyed
 * by this enum. Defining the set here first, on its own, is deliberate:
 * it's the one artifact every later task (preferences, digesting, reseller
 * mail-template overrides) needs to already exist and be stable.
 */
enum NotificationType: string
{
    case Welcome = 'welcome';
    case Invite = 'invite';
    case Assignment = 'assignment';
    case Deadline = 'deadline';
    case Overdue = 'overdue';
    case Completion = 'completion';
    case Certificate = 'certificate';
    case PasswordReset = 'password_reset';
    case Billing = 'billing';
    case AdminAlert = 'admin_alert';

    public function label(): string
    {
        return match ($this) {
            self::Welcome => 'Welcome',
            self::Invite => 'Invitation',
            self::Assignment => 'Course assigned',
            self::Deadline => 'Deadline approaching',
            self::Overdue => 'Assignment overdue',
            self::Completion => 'Course completed',
            self::Certificate => 'Certificate issued',
            self::PasswordReset => 'Password reset',
            self::Billing => 'Billing',
            self::AdminAlert => 'Admin alert',
        };
    }
}
