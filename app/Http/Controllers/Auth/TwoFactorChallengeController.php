<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CompleteTwoFactorChallenge;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorChallengeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('login.id')) {
            return to_route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(TwoFactorChallengeRequest $request, CompleteTwoFactorChallenge $complete): RedirectResponse
    {
        $complete($request->toDto());

        $request->session()->regenerate();

        return redirect()->intended('/');
    }
}
