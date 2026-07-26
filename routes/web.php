<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ImpersonationController;
use App\Http\Controllers\Auth\InviteAcceptController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RecoveryCodeController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\InvitesController;
use App\Http\Controllers\ResellerKlantController;
use App\Http\Middleware\EnsureTwoFactorAuthenticationIsEnabled;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/login/{slug}', TenantLoginController::class)->name('tenant.login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');

    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])
        ->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
        ->middleware('throttle:two-factor-challenge');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.store');

    Route::get('/invite/{reseller}/accept/{invite}/{hash}', [InviteAcceptController::class, 'show'])
        ->middleware('signed')
        ->name('invite.accept');
    Route::post('/invite/{reseller}/accept/{invite}/{hash}', [InviteAcceptController::class, 'store'])
        ->middleware(['signed', 'throttle:invite-accept']);
});

Route::middleware(['auth', EnsureTwoFactorAuthenticationIsEnabled::class])->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/two-factor', [TwoFactorAuthenticationController::class, 'show'])->name('two-factor.show');
        Route::post('/two-factor', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware('throttle:two-factor-manage')
            ->name('two-factor.store');
        Route::post('/two-factor/confirm', [TwoFactorAuthenticationController::class, 'confirm'])
            ->middleware('throttle:two-factor-manage')
            ->name('two-factor.confirm');
        Route::delete('/two-factor', [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware('throttle:two-factor-manage')
            ->name('two-factor.destroy');
        Route::post('/two-factor/recovery-codes', [RecoveryCodeController::class, 'store'])
            ->middleware('throttle:two-factor-manage')
            ->name('two-factor.recovery-codes');
    });

    // Reference vertical slice (FormRequest -> DTO -> Action -> Repository
    // -> Inertia Resource), copyable for later features -- see CLAUDE.md §0.
    Route::get('/klanten', [ResellerKlantController::class, 'index'])
        ->middleware('can:viewAny,App\Models\ResellerKlant')
        ->name('klanten.index');

    Route::post('/klanten', [ResellerKlantController::class, 'store'])
        ->name('klanten.store');

    Route::get('/invites', [InvitesController::class, 'index'])
        ->middleware('can:viewAny,App\Models\UserInvite')
        ->name('invites.index');

    Route::post('/invites', [InvitesController::class, 'store'])->name('invites.store');

    Route::delete('/invites/{invite}', [InvitesController::class, 'destroy'])->name('invites.destroy');

    Route::post('/impersonate/{user}', [ImpersonationController::class, 'store'])
        ->middleware('throttle:impersonate')
        ->name('impersonate.start');

    Route::delete('/impersonate', [ImpersonationController::class, 'destroy'])->name('impersonate.stop');
});
