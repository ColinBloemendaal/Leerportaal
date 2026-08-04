<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * CLAUDE.md §1: "Page builder: Level 1 only (theme config). Level 2
 * (block templates) is Phase 9." A fixed set of template slots per
 * reseller, never an arbitrary custom-page slug -- see the pages table
 * migration's own comment.
 */
enum PageTemplate: string
{
    case Home = 'home';
    case CourseOverview = 'course_overview';
    case Login = 'login';
    case About = 'about';
    case Contact = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Home',
            self::CourseOverview => 'Course overview',
            self::Login => 'Login',
            self::About => 'About',
            self::Contact => 'Contact',
        };
    }
}
