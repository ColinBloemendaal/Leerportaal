<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AcceptInvite;
use App\Contracts\Repositories\ResellerRepository;
use App\Contracts\Repositories\UserInviteRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AcceptInviteRequest;
use App\Models\UserInvite;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class InviteAcceptController extends Controller
{
    public function show(
        Request $request,
        string $reseller,
        int $invite,
        string $hash,
        ResellerRepository $resellers,
        UserInviteRepository $invites,
    ): Response|RedirectResponse {
        $userInvite = $this->resolveInvite($reseller, $invite, $hash, $resellers, $invites);

        if ($userInvite === null) {
            return to_route('login')->with('status', __('This invite is no longer valid.'));
        }

        return Inertia::render('Auth/AcceptInvite', [
            // Includes the signed query string (expires/signature) -- the
            // form must POST back to this exact URL for `signed` middleware
            // to accept it.
            'acceptUrl' => $request->fullUrl(),
            'name' => $userInvite->name,
            'email' => $userInvite->email,
        ]);
    }

    public function store(
        AcceptInviteRequest $request,
        string $reseller,
        AcceptInvite $acceptInvite,
        ResellerRepository $resellers,
        StatefulGuard $guard,
    ): RedirectResponse {
        // The invite itself is the credential establishing which reseller
        // this guest belongs to -- same bootstrapping idea as /login/{slug}
        // (TenantLoginController), just without a round trip through a
        // cookie first, since we already have the slug in this URL.
        $resolvedReseller = $resellers->findActiveBySlug($reseller);
        abort_if($resolvedReseller === null, 404);
        app(TenantContext::class)->set($resolvedReseller);

        $user = $acceptInvite($request->toDto());

        $guard->login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', __('Welcome!'));
    }

    private function resolveInvite(
        string $reseller,
        int $invite,
        string $hash,
        ResellerRepository $resellers,
        UserInviteRepository $invites,
    ): ?UserInvite {
        $resolvedReseller = $resellers->findActiveBySlug($reseller);

        if ($resolvedReseller === null) {
            return null;
        }

        app(TenantContext::class)->set($resolvedReseller);

        $userInvite = $invites->findPendingById($invite);

        if ($userInvite === null || ! hash_equals(sha1($userInvite->email), $hash)) {
            return null;
        }

        return $userInvite;
    }
}
