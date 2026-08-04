<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Gdpr\AcceptDataProcessingAgreement;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CLAUDE.md §8 (GDPR): "Per-reseller DPA acceptance and record." The DPA
 * text itself is a static, in-repo legal document (docs/dpa.md), same
 * spirit as docs/subprocessors.md -- this controller just surfaces it
 * and records acceptance, it doesn't manage document versions itself.
 */
final class DataProcessingAgreementController extends Controller
{
    public function show(TenantContext $tenantContext): Response
    {
        $reseller = $tenantContext->get();
        abort_if($reseller === null, 404);

        $this->authorize('manageDpa', $reseller);

        return Inertia::render('Settings/Dpa', [
            'document' => File::get(base_path('docs/dpa.md')),
            'currentVersion' => config('gdpr.dpa_version'),
            'acceptedVersion' => $reseller->dpa_accepted_version,
            'acceptedAt' => $reseller->dpa_accepted_at?->toIso8601String(),
            'needsAcceptance' => ! $reseller->hasAcceptedCurrentDpa(),
        ]);
    }

    public function accept(Request $request, TenantContext $tenantContext, AcceptDataProcessingAgreement $accept): RedirectResponse
    {
        $reseller = $tenantContext->get();
        abort_if($reseller === null, 404);

        $this->authorize('manageDpa', $reseller);

        $user = $request->user();
        abort_if($user === null, 401);

        $accept($reseller, $user);

        return to_route('settings.dpa.show')->with('success', __('Data Processing Agreement accepted.'));
    }
}
