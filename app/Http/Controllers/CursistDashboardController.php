<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Progress\CursistDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Every authenticated user (platform staff, reseller staff, cursist)
 * can see their own dashboard -- there's no authorization decision
 * beyond "is logged in", so no Policy is involved.
 */
final class CursistDashboardController extends Controller
{
    public function index(Request $request, CursistDashboardService $dashboard): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'assignments' => $user === null ? [] : $dashboard->forUser($user)->values(),
        ]);
    }
}
