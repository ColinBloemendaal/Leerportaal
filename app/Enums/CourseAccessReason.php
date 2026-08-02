<?php

declare(strict_types=1);

namespace App\Enums;

enum CourseAccessReason: string
{
    case OwnedByReseller = 'owned_by_reseller';
    case DirectGrant = 'direct_grant';
    case CategoryGrant = 'category_grant';
    case NoAccess = 'no_access';

    public function grantsAccess(): bool
    {
        return $this !== self::NoAccess;
    }

    public function label(): string
    {
        return match ($this) {
            self::OwnedByReseller => 'Reseller-authored course',
            self::DirectGrant => 'Directly granted by platform',
            self::CategoryGrant => 'Granted via category',
            self::NoAccess => 'No access',
        };
    }
}
