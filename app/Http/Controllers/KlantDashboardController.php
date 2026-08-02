<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\ResellerKlantRepository;
use App\Services\Reporting\KlantDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class KlantDashboardController extends Controller
{
    public function index(Request $request, ResellerKlantRepository $resellerKlanten, KlantDashboardService $dashboard): Response
    {
        $user = $request->user();

        abort_if($user === null, 404);

        $klant = $resellerKlanten->findOwnKlant($user);

        abort_if($klant === null, 404);

        $this->authorize('view', $klant);

        return Inertia::render('Admin/Klant/Dashboard', [
            'stats' => $dashboard->forKlant($klant),
        ]);
    }
}
