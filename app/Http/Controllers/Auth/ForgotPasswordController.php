<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ForgotPasswordController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    /**
     * Always responds the same way regardless of whether the email
     * exists, to avoid leaking which addresses have accounts.
     */
    public function store(ForgotPasswordRequest $request, SendPasswordResetLink $sendResetLink): RedirectResponse
    {
        $sendResetLink($request->toDto());

        return back()->with('status', __('If that email address is in our system, we sent a password reset link.'));
    }
}
