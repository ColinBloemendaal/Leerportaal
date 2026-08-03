<?php

use App\Http\Controllers\ActivityLogIndexController;
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
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\CourseAssignmentIndexController;
use App\Http\Controllers\CourseIndexController;
use App\Http\Controllers\CursistDashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvitesController;
use App\Http\Controllers\KlantDashboardController;
use App\Http\Controllers\MailWebhookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\PlatformDashboardController;
use App\Http\Controllers\PlatformHealthController;
use App\Http\Controllers\QuizAttemptIndexController;
use App\Http\Controllers\ResellerBrandingController;
use App\Http\Controllers\ResellerDashboardController;
use App\Http\Controllers\ResellerIndexController;
use App\Http\Controllers\ResellerKlantController;
use App\Http\Controllers\ResellerMailTemplateController;
use App\Http\Controllers\ResellerThemeController;
use App\Http\Controllers\UserDetailController;
use App\Http\Controllers\UserIndexController;
use App\Http\Middleware\EnsureTwoFactorAuthenticationIsEnabled;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/login/{slug}', TenantLoginController::class)->name('tenant.login');

// Public, unauthenticated -- see App\Http\Controllers\ResellerBrandingController.
Route::get('/branding/{reseller}/logo', [ResellerBrandingController::class, 'logo'])->name('branding.logo');
Route::get('/branding/{reseller}/favicon', [ResellerBrandingController::class, 'favicon'])->name('branding.favicon');
Route::get('/branding/{reseller}/login-background', [ResellerBrandingController::class, 'loginBackground'])
    ->name('branding.login-background');

// Public, unauthenticated -- see App\Http\Controllers\CertificateVerificationController.
Route::get('/certificates/verify/{code}', [CertificateVerificationController::class, 'show'])
    ->name('certificates.verify');

// Public, unauthenticated, signature-verified instead (see MailWebhookController
// and bootstrap/app.php's CSRF exception for this path).
Route::post('/webhooks/mailgun', [MailWebhookController::class, 'mailgun'])
    ->middleware('throttle:mailgun-webhook')
    ->name('webhooks.mailgun');

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

        Route::get('/theme', [ResellerThemeController::class, 'edit'])->name('theme.edit');
        Route::put('/theme', [ResellerThemeController::class, 'update'])->name('theme.update');

        Route::get('/notifications', [NotificationPreferenceController::class, 'edit'])->name('notifications.edit');
        Route::put('/notifications', [NotificationPreferenceController::class, 'update'])->name('notifications.update');
        Route::put('/notifications/digest-frequency', [NotificationPreferenceController::class, 'updateDigestFrequency'])
            ->name('notifications.digest-frequency');

        Route::get('/email-templates', [ResellerMailTemplateController::class, 'index'])
            ->name('email-templates.index');
        Route::get('/email-templates/{type}', [ResellerMailTemplateController::class, 'edit'])
            ->name('email-templates.edit');
        Route::put('/email-templates/{type}', [ResellerMailTemplateController::class, 'update'])
            ->name('email-templates.update');
    });

    // Reference vertical slice (FormRequest -> DTO -> Action -> Repository
    // -> Inertia Resource), copyable for later features -- see CLAUDE.md §0.
    Route::get('/klanten', [ResellerKlantController::class, 'index'])
        ->middleware('can:viewAny,App\Models\ResellerKlant')
        ->name('klanten.index');

    Route::post('/klanten', [ResellerKlantController::class, 'store'])
        ->name('klanten.store');

    Route::delete('/klanten/{klant}', [ResellerKlantController::class, 'destroy'])->name('klanten.destroy');

    Route::post('/klanten/{klant}/restore', [ResellerKlantController::class, 'restore'])->name('klanten.restore');

    Route::get('/invites', [InvitesController::class, 'index'])
        ->middleware('can:viewAny,App\Models\UserInvite')
        ->name('invites.index');

    Route::post('/invites', [InvitesController::class, 'store'])->name('invites.store');

    Route::delete('/invites/{invite}', [InvitesController::class, 'destroy'])->name('invites.destroy');

    Route::post('/invites/{invite}/restore', [InvitesController::class, 'restore'])->name('invites.restore');

    Route::post('/impersonate/{user}', [ImpersonationController::class, 'store'])
        ->middleware('throttle:impersonate')
        ->name('impersonate.start');

    Route::delete('/impersonate', [ImpersonationController::class, 'destroy'])->name('impersonate.stop');

    Route::get('/dashboard', [CursistDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('admin/exports')->name('admin.exports.')->group(function () {
        Route::get('/', [ExportController::class, 'index'])->name('index');
        Route::post('/', [ExportController::class, 'store'])->name('store');
        Route::get('/{export}/download', [ExportController::class, 'download'])
            ->middleware('signed')
            ->name('download');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
    });

    Route::prefix('admin/platform')->name('admin.platform.')->group(function () {
        Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');
        Route::get('/resellers', [ResellerIndexController::class, 'index'])->name('resellers.index');
        Route::get('/users', [UserIndexController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserDetailController::class, 'show'])->name('users.show');
        Route::get('/activity', [ActivityLogIndexController::class, 'index'])->name('activity.index');
        Route::get('/health', [PlatformHealthController::class, 'index'])->name('health');
    });

    Route::prefix('admin/reseller')->name('admin.reseller.')->group(function () {
        Route::get('/', [ResellerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/courses', [CourseIndexController::class, 'index'])->name('courses.index');
        Route::get('/assignments', [CourseAssignmentIndexController::class, 'index'])->name('assignments.index');
        Route::get('/attempts', [QuizAttemptIndexController::class, 'index'])->name('attempts.index');
    });

    Route::prefix('admin/klant')->name('admin.klant.')->group(function () {
        Route::get('/', [KlantDashboardController::class, 'index'])->name('dashboard');
    });
});
