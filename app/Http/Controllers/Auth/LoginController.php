<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class LoginController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request, AuthenticateUser $authenticate, StatefulGuard $guard): RedirectResponse
    {
        $data = $request->toDto();
        $user = $authenticate($data);

        if ($user->hasEnabledTwoFactorAuthentication()) {
            $request->session()->put('login.id', $user->getKey());
            $request->session()->put('login.remember', $data->remember);

            return to_route('two-factor.challenge');
        }

        $guard->login($user, $data->remember);
        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}
