<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The full role set from TODO.md's "Roles & permissions" phase (task 34).
 * Not enforced anywhere yet -- spatie/laravel-permission isn't installed
 * until that task. Exists now only so the invite flow (task 33) has a
 * fixed set of values to store as the invitee's intended role; task 34
 * should seed these exact cases as permission roles rather than
 * reinventing the list.
 */
enum Role: string
{
    case SuperAdmin = 'super-admin';
    case PlatformAdmin = 'platform-admin';
    case PlatformAuthor = 'platform-author';
    case Support = 'support';
    case ResellerOwner = 'reseller-owner';
    case ResellerAdmin = 'reseller-admin';
    case ResellerAuthor = 'reseller-author';
    case ResellerReporter = 'reseller-reporter';
    case KlantAdmin = 'klant-admin';
    case KlantManager = 'klant-manager';
    case Cursist = 'cursist';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super admin',
            self::PlatformAdmin => 'Platform admin',
            self::PlatformAuthor => 'Platform author',
            self::Support => 'Support',
            self::ResellerOwner => 'Reseller owner',
            self::ResellerAdmin => 'Reseller admin',
            self::ResellerAuthor => 'Reseller author',
            self::ResellerReporter => 'Reseller reporter',
            self::KlantAdmin => 'Klant admin',
            self::KlantManager => 'Klant manager',
            self::Cursist => 'Cursist',
        };
    }

    /**
     * Roles selectable when inviting a user into a resellerklant
     * (resellerklant_id set). The reseller-side-staff roles below are
     * only for reseller-level invites (resellerklant_id null).
     *
     * @return list<self>
     */
    public static function klantRoles(): array
    {
        return [self::KlantAdmin, self::KlantManager, self::Cursist];
    }

    /**
     * Roles selectable when inviting reseller-side staff (no
     * resellerklant). Platform roles are never sent through the invite
     * flow -- see UserInvitePolicy.
     *
     * @return list<self>
     */
    public static function resellerRoles(): array
    {
        return [self::ResellerOwner, self::ResellerAdmin, self::ResellerAuthor, self::ResellerReporter];
    }
}
