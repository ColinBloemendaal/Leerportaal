<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Theming\UpdateResellerTheme;
use App\Contracts\Repositories\ResellerThemeRepository;
use App\Http\Requests\Theming\UpdateResellerThemeRequest;
use App\Http\Resources\ResellerThemeResource;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ResellerThemeController extends Controller
{
    public function edit(ResellerThemeRepository $themes): Response
    {
        $this->authorize('viewAny', 'App\Models\ResellerTheme');

        return Inertia::render('Settings/Theme', [
            'theme' => new ResellerThemeResource($themes->findForCurrentResellerOrDefault()),
        ]);
    }

    public function update(UpdateResellerThemeRequest $request, UpdateResellerTheme $updateResellerTheme): RedirectResponse
    {
        $updateResellerTheme($request->toDto());

        return to_route('settings.theme.edit')->with('success', __('Theme updated.'));
    }
}
