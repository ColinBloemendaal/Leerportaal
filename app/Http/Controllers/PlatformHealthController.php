<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Reporting\PlatformHealthService;
use Inertia\Inertia;
use Inertia\Response;

final class PlatformHealthController extends Controller
{
    public function index(PlatformHealthService $health): Response
    {
        // Same access boundary as the rest of the platform admin area.
        $this->authorize('viewAny', 'App\Models\Reseller');

        return Inertia::render('Admin/Platform/Health/Index', [
            'health' => $health->snapshot(),
        ]);
    }
}
