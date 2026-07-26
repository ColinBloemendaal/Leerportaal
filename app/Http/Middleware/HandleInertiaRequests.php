<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name'),
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'success' => fn () => $request->session()->get('success'),
            ],
            // Impersonation banner. Reads session values set by
            // App\Actions\Auth\StartImpersonation directly, rather than
            // querying the User record here -- this middleware isn't in
            // the "who may touch Models" arch whitelist, and doesn't need
            // to be for this.
            'impersonation' => fn () => $request->session()->has('impersonation_id') ? [
                'impersonatorName' => $request->session()->get('impersonator_name'),
                'targetName' => $request->session()->get('impersonated_name'),
            ] : null,
        ];
    }
}
