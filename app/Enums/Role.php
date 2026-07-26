<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The full role set from TODO.md's "Roles & permissions" phase.
 *
 * Two different enforcement mechanisms, split by whether a reseller_id
 * exists to scope by:
 * - Reseller-side roles (resellerRoles() + klantRoles(), together
 *   teamRoles()) are real spatie/laravel-permission roles, teams-scoped
 *   by reseller_id -- seeded per reseller by
 *   App\Actions\Permissions\SeedRolesForReseller.
 * - Platform roles (platformRoles()) are stored directly on
 *   users.platform_role instead. spatie's teams mode makes the team
 *   foreign key NOT NULL and part of the pivot tables' composite primary
 *   key, so platform staff (reseller_id null) structurally cannot be
 *   assigned a teams-scoped role -- this was a deliberate choice to
 *   avoid fighting that assumption (human decision), not an oversight.
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

    /**
     * Every reseller-side role, teams-scoped by reseller_id -- the set
     * seeded as real spatie roles whenever a reseller is created. See
     * App\Actions\Permissions\SeedRolesForReseller.
     *
     * @return list<self>
     */
    public static function teamRoles(): array
    {
        return [...self::resellerRoles(), ...self::klantRoles()];
    }

    /**
     * Stored on users.platform_role, not as spatie roles -- see the class
     * docblock for why.
     *
     * @return list<self>
     */
    public static function platformRoles(): array
    {
        return [self::SuperAdmin, self::PlatformAdmin, self::PlatformAuthor, self::Support];
    }
}
