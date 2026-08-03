<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Billing\PaymentGateway;
use App\Contracts\Dns\DnsResolver;
use App\Contracts\Mail\MailSuppressionWebhookParser;
use App\Contracts\Ploi\PloiClient;
use App\Contracts\Repositories\ResellerThemeRepository;
use App\Contracts\Storage\StorageMetering;
use App\Listeners\Mail\PreventSendingToSuppressedAddresses;
use App\Policies\SuperAdminBypass;
use App\Services\Billing\MollieGateway;
use App\Services\Dns\NativeDnsResolver;
use App\Services\Mail\MailgunWebhookParser;
use App\Services\Ploi\HttpPloiClient;
use App\Services\Storage\EloquentStorageMeteringService;
use App\Services\Theming\ThemeCssGenerator;
use App\Tenancy\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
        $this->app->bind(DnsResolver::class, NativeDnsResolver::class);
        $this->app->bind(StorageMetering::class, EloquentStorageMeteringService::class);

        $this->app->bind(PloiClient::class, fn ($app): HttpPloiClient => new HttpPloiClient(
            $app->make(HttpFactory::class),
            (string) config('ploi.api_key'),
            (string) config('ploi.server_id'),
            (string) config('ploi.site_id'),
        ));

        // Illuminate\Auth\Passwords\PasswordResetServiceProvider only
        // registers string aliases ('auth.password.broker'), not this
        // interface -- bound here so Actions can type-hint it cleanly.
        $this->app->bind(PasswordBroker::class, fn ($app) => $app->make('auth.password.broker'));

        // Illuminate\Auth\AuthServiceProvider doesn't bind this interface
        // either -- the default guard ('web') implements it.
        $this->app->bind(StatefulGuard::class, fn ($app) => $app->make(AuthFactory::class)->guard());

        // Illuminate\Filesystem\FilesystemServiceProvider only registers
        // the 'filesystem' string alias, not this interface.
        $this->app->bind(FilesystemFactory::class, fn ($app) => $app->make('filesystem'));

        $this->app->bind(MailSuppressionWebhookParser::class, fn ($app): MailgunWebhookParser => new MailgunWebhookParser(
            (string) config('services.mailgun.webhook_signing_key'),
        ));

        $this->app->bind(PaymentGateway::class, fn ($app): MollieGateway => new MollieGateway(
            $app->make(HttpFactory::class),
            (string) config('services.mollie.key'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        $this->configureRateLimiting();

        Gate::before($this->app->make(SuperAdminBypass::class));

        Event::listen(MessageSending::class, PreventSendingToSuppressedAddresses::class);

        // Runtime CSS custom property injection (CLAUDE.md §1: "no
        // per-tenant CSS build step"), computed fresh per request from the
        // current reseller's theme.
        ViewFacade::composer('app', function (View $view): void {
            $theme = $this->app->make(ResellerThemeRepository::class)->findForCurrentReseller();
            $css = $this->app->make(ThemeCssGenerator::class)->generate($theme);

            $view->with('themeCss', $css);
        });
    }

    private function configureRateLimiting(): void
    {
        // Keyed by email+IP (not IP alone) so one attacker can't lock out
        // every user behind a shared IP, and not email alone so an
        // attacker can't lock out a known victim from any IP.
        RateLimiter::for('login', function (Request $request): Limit {
            $key = Str::transliterate(Str::lower((string) $request->string('email'))).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('password-reset', function (Request $request): Limit {
            $key = Str::transliterate(Str::lower((string) $request->string('email'))).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });

        // Unauthenticated at this point (mid-login) -- keyed by session,
        // since that's what ties the request to the pending login.
        RateLimiter::for('two-factor-challenge', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->session()->getId().'|'.$request->ip());
        });

        // Authenticated settings endpoints (confirm/enable/disable/regenerate).
        RateLimiter::for('two-factor-manage', function (Request $request): Limit {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(5)->by((string) $key);
        });

        // Signed link, so brute-forcing the hash isn't really feasible --
        // this is just baseline hygiene, not the primary defense.
        RateLimiter::for('invite-accept', function (Request $request): Limit {
            return Limit::perMinute(10)->by((string) $request->ip());
        });

        // A powerful, sensitive action -- keyed by the impersonator, not
        // the target, since the same actor starting many sessions in a
        // row (against different targets) is the pattern worth limiting.
        RateLimiter::for('impersonate', function (Request $request): Limit {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(5)->by((string) $key);
        });

        // Signature verification is the real defense; this is just baseline
        // abuse protection against someone hammering the endpoint. Generous
        // limit -- a real bounce/complaint spike from one send can trigger
        // many legitimate webhook calls in a short window.
        RateLimiter::for('mailgun-webhook', function (Request $request): Limit {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
