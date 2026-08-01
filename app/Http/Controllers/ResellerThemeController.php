<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Theming\UpdateResellerTheme;
use App\Contracts\Repositories\ResellerThemeRepository;
use App\Http\Requests\Theming\UpdateResellerThemeRequest;
use App\Http\Resources\ResellerThemeResource;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ResellerThemeController extends Controller
{
    public function edit(ResellerThemeRepository $themes, TenantContext $tenantContext): Response
    {
        $this->authorize('viewAny', 'App\Models\ResellerTheme');

        $theme = $themes->findForCurrentResellerOrDefault();
        $slug = $tenantContext->get()?->slug;

        return Inertia::render('Settings/Theme', [
            'theme' => new ResellerThemeResource($theme),
            'assetUrls' => [
                'logo' => $slug !== null && $theme->logo_path !== null ? route('branding.logo', $slug) : null,
                'favicon' => $slug !== null && $theme->favicon_path !== null ? route('branding.favicon', $slug) : null,
                'login_background' => $slug !== null && $theme->login_background_path !== null
                    ? route('branding.login-background', $slug)
                    : null,
            ],
        ]);
    }

    public function update(UpdateResellerThemeRequest $request, UpdateResellerTheme $updateResellerTheme): RedirectResponse
    {
        $updateResellerTheme($request->toDto());

        return to_route('settings.theme.edit')->with('success', __('Theme updated.'));
    }
}
