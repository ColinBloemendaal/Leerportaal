<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Billing\ResellerBillingDashboardService;
use App\Tenancy\TenantContext;
use Inertia\Inertia;
use Inertia\Response;

final class ResellerBillingDashboardController extends Controller
{
    public function index(ResellerBillingDashboardService $dashboard, TenantContext $tenantContext): Response
    {
        $this->authorize('viewAny', 'App\Models\ResellerKlant');

        $resellerId = $tenantContext->id();

        // Boundary check, not defensive dead code: this route only ever
        // runs behind reseller-authenticated middleware, but id() itself
        // is nullable (also used from unauthenticated contexts).
        abort_if($resellerId === null, 404);

        return Inertia::render('Admin/Reseller/Billing/Dashboard', [
            'stats' => $dashboard->forReseller($resellerId),
        ]);
    }
}
