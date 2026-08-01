<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\ResellerRepository;
use App\Contracts\Repositories\ResellerThemeRepository;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Public, unauthenticated: the login page (and any other unbranded-visitor
 * page) needs to render a reseller's logo/favicon/login background before
 * anyone is signed in. These assets live on the private disk regardless
 * (CLAUDE.md §7) -- this route is what makes them visible without ever
 * exposing a directly guessable public URL for the disk itself. See
 * App\Actions\Theming\UpdateResellerTheme for where they're written.
 */
final class ResellerBrandingController extends Controller
{
    private const DISK = 'local';

    public function logo(string $reseller, ResellerRepository $resellers, ResellerThemeRepository $themes): StreamedResponse
    {
        $resolvedReseller = $resellers->findActiveBySlug($reseller);
        abort_if($resolvedReseller === null, 404);
        app(TenantContext::class)->set($resolvedReseller);

        $theme = $themes->findForCurrentReseller();

        return $this->stream($theme?->logo_path);
    }

    public function favicon(string $reseller, ResellerRepository $resellers, ResellerThemeRepository $themes): StreamedResponse
    {
        $resolvedReseller = $resellers->findActiveBySlug($reseller);
        abort_if($resolvedReseller === null, 404);
        app(TenantContext::class)->set($resolvedReseller);

        $theme = $themes->findForCurrentReseller();

        return $this->stream($theme?->favicon_path);
    }

    public function loginBackground(string $reseller, ResellerRepository $resellers, ResellerThemeRepository $themes): StreamedResponse
    {
        $resolvedReseller = $resellers->findActiveBySlug($reseller);
        abort_if($resolvedReseller === null, 404);
        app(TenantContext::class)->set($resolvedReseller);

        $theme = $themes->findForCurrentReseller();

        return $this->stream($theme?->login_background_path);
    }

    private function stream(?string $path): StreamedResponse
    {
        $disk = Storage::disk(self::DISK);

        abort_if($path === null || ! $disk->exists($path), 404);

        return $disk->response($path);
    }
}
