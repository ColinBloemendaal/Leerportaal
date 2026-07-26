<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The granular permission catalog from TODO.md's "Roles & permissions"
 * phase. Seeded as spatie/laravel-permission Permission rows by the
 * 2026_07_26_160000_seed_permissions_table migration -- see that file for
 * why this lives in a migration rather than a seeder.
 *
 * Courses/reports/billing permissions are placeholders: no Course,
 * Report, or Billing model exists yet (later phases), so nothing checks
 * these today. They exist now only so the catalog itself, and the
 * policies that will eventually check it, don't have to be invented
 * later under time pressure. Not assigned to any role yet -- there's
 * nothing to assign them to, since the policies that would consume them
 * don't exist until those phases.
 */
enum Permission: string
{
    case CoursesView = 'courses.view';
    case CoursesCreate = 'courses.create';
    case CoursesUpdate = 'courses.update';
    case CoursesDelete = 'courses.delete';
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';
    case ReportsView = 'reports.view';
    case BillingView = 'billing.view';
    case BillingManage = 'billing.manage';
    case Impersonate = 'impersonate';

    public function label(): string
    {
        return match ($this) {
            self::CoursesView => 'View courses',
            self::CoursesCreate => 'Create courses',
            self::CoursesUpdate => 'Edit courses',
            self::CoursesDelete => 'Delete courses',
            self::UsersView => 'View users',
            self::UsersCreate => 'Create users',
            self::UsersUpdate => 'Edit users',
            self::UsersDelete => 'Delete users',
            self::ReportsView => 'View reports',
            self::BillingView => 'View billing',
            self::BillingManage => 'Manage billing',
            self::Impersonate => 'Impersonate users',
        };
    }
}
