<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ConfirmTwoFactorAuthentication;
use App\Actions\Auth\DisableTwoFactorAuthentication;
use App\Actions\Auth\EnableTwoFactorAuthentication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmTwoFactorAuthenticationRequest;
use App\Models\User;
use App\Services\Auth\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TwoFactorAuthenticationController extends Controller
{
    public function show(Request $request, TwoFactorAuthenticator $authenticator): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $pending = $user->two_factor_secret !== null && ! $user->hasEnabledTwoFactorAuthentication();

        return Inertia::render('Settings/TwoFactorAuthentication', [
            'enabled' => $user->hasEnabledTwoFactorAuthentication(),
            'pending' => $pending,
            'qrCodeSvg' => $pending
                ? $authenticator->qrCodeSvg((string) config('app.name'), $user->email, (string) $user->two_factor_secret)
                : null,
            'recoveryCodes' => $request->session()->get('recoveryCodes'),
        ]);
    }

    public function store(Request $request, EnableTwoFactorAuthentication $enable): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $user = $enable($user);

        return to_route('settings.two-factor.show')->with('recoveryCodes', $user->two_factor_recovery_codes);
    }

    public function confirm(
        ConfirmTwoFactorAuthenticationRequest $request,
        ConfirmTwoFactorAuthentication $confirm,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $confirm($user, $request->toDto());

        return to_route('settings.two-factor.show')->with('status', __('Two-factor authentication confirmed.'));
    }

    public function destroy(Request $request, DisableTwoFactorAuthentication $disable): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $disable($user);

        return to_route('settings.two-factor.show');
    }
}
