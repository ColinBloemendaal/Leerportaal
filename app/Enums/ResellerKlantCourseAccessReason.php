<?php

declare(strict_types=1);

namespace App\Enums;

enum ResellerKlantCourseAccessReason: string
{
    case GrantedToKlant = 'granted_to_klant';
    case ResellerLacksAccess = 'reseller_lacks_access';
    case NotGrantedToKlant = 'not_granted_to_klant';

    public function grantsAccess(): bool
    {
        return $this === self::GrantedToKlant;
    }

    public function label(): string
    {
        return match ($this) {
            self::GrantedToKlant => 'Granted to this klant',
            self::ResellerLacksAccess => 'Reseller itself has no access to this course',
            self::NotGrantedToKlant => 'Reseller has access, but has not granted it to this klant',
        };
    }
}
