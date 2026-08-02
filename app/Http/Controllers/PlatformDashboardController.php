<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Reporting\PlatformDashboardService;
use Inertia\Inertia;
use Inertia\Response;

final class PlatformDashboardController extends Controller
{
    public function index(PlatformDashboardService $dashboard): Response
    {
        $this->authorize('viewAny', 'App\Models\Reseller');

        return Inertia::render('Admin/Platform/Dashboard', [
            'stats' => $dashboard->snapshot(),
        ]);
    }
}
