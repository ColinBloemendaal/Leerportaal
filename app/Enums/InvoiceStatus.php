<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Issued => 'Issued',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Void => 'Void',
        };
    }

    /**
     * A draft is the only status still open for new lines and the only one
     * a billable event may attach to -- everything else is either awaiting
     * payment or already settled/void.
     */
    public function isOpenForNewLines(): bool
    {
        return $this === self::Draft;
    }
}
