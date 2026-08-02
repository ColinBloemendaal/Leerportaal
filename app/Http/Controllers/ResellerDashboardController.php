<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Reporting\ResellerDashboardService;
use Inertia\Inertia;
use Inertia\Response;

final class ResellerDashboardController extends Controller
{
    public function index(ResellerDashboardService $dashboard): Response
    {
        $this->authorize('viewAny', 'App\Models\ResellerKlant');

        return Inertia::render('Admin/Reseller/Dashboard', [
            'stats' => $dashboard->snapshot(),
        ]);
    }
}
