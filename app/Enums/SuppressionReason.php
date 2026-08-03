<?php

declare(strict_types=1);

namespace App\Enums;

enum SuppressionReason: string
{
    case HardBounce = 'hard_bounce';
    case Complaint = 'complaint';

    public function label(): string
    {
        return match ($this) {
            self::HardBounce => 'Hard bounce',
            self::Complaint => 'Spam complaint',
        };
    }
}
