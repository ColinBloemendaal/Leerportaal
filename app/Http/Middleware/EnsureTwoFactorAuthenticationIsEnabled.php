<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mandatory for platform staff (proxy for "platform roles" until real
 * roles exist -- Phase 1 task 34), optional for reseller-side users.
 * Redirects to the setup page instead of blocking outright, since the
 * user has no way to comply otherwise.
 */
final class EnsureTwoFactorAuthenticationIsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null
            && $user->requiresTwoFactorAuthentication()
            && ! $user->hasEnabledTwoFactorAuthentication()
            && ! $request->routeIs('settings.two-factor.*', 'logout')
        ) {
            return redirect()->route('settings.two-factor.show');
        }

        return $next($request);
    }
}
