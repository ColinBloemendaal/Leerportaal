<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\CustomDomainRepository;
use App\Contracts\Repositories\ResellerKlantRepository;
use App\Contracts\Repositories\ResellerRepository;
use App\Contracts\Repositories\UserInviteRepository;
use App\Repositories\Eloquent\EloquentCustomDomainRepository;
use App\Repositories\Eloquent\EloquentResellerKlantRepository;
use App\Repositories\Eloquent\EloquentResellerRepository;
use App\Repositories\Eloquent\EloquentUserInviteRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds each repository interface in App\Contracts\Repositories to its
 * Eloquent implementation in App\Repositories\Eloquent. Add one
 * $this->app->bind() call per repository -- see CLAUDE.md §3a for when a
 * repository is warranted.
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ResellerRepository::class, EloquentResellerRepository::class);
        $this->app->bind(CustomDomainRepository::class, EloquentCustomDomainRepository::class);
        $this->app->bind(ResellerKlantRepository::class, EloquentResellerKlantRepository::class);
        $this->app->bind(UserInviteRepository::class, EloquentUserInviteRepository::class);
    }
}
