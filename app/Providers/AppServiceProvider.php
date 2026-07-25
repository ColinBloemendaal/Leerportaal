<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Dns\DnsResolver;
use App\Contracts\Ploi\PloiClient;
use App\Services\Dns\NativeDnsResolver;
use App\Services\Ploi\HttpPloiClient;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
        $this->app->bind(DnsResolver::class, NativeDnsResolver::class);

        $this->app->bind(PloiClient::class, fn ($app): HttpPloiClient => new HttpPloiClient(
            $app->make(HttpFactory::class),
            (string) config('ploi.api_key'),
            (string) config('ploi.server_id'),
            (string) config('ploi.site_id'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
    }
}
