<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The indexes this session's generic filter/sort/search layer applies
 * to -- see TODO.md Phase 7's "Filterable index for: users, resellers,
 * klanten, courses, assignments, attempts, invoices, activity". A saved
 * filter is validated against this set rather than an arbitrary string,
 * so a preset can never point at a resource type that doesn't exist.
 */
enum FilterableResource: string
{
    case Users = 'users';
    case Resellers = 'resellers';
    case Klanten = 'klanten';
    case Courses = 'courses';
    case Assignments = 'assignments';
    case Attempts = 'attempts';
    case Invoices = 'invoices';
    case Activity = 'activity';
}
