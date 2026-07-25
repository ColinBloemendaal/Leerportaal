<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Contracts\Repositories\ResellerRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

final class TenantLoginController extends Controller
{
    public function __invoke(string $slug, ResellerRepository $resellers): RedirectResponse
    {
        $reseller = $resellers->findActiveBySlug($slug);

        abort_if($reseller === null, 404);

        return redirect('/login')
            ->withCookie(cookie('reseller_slug', $reseller->slug, 60 * 24 * 30));
    }
}
