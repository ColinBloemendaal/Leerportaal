<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Binds each repository interface in App\Contracts\Repositories to its
 * Eloquent implementation in App\Repositories\Eloquent. Add one
 * $this->app->bind() call per repository -- see CLAUDE.md §3a for when a
 * repository is warranted.
 *
 * Example:
 *   $this->app->bind(CourseRepository::class, EloquentCourseRepository::class);
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
}
