<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Exporting\RequestExport;
use App\Contracts\Repositories\ExportRepository;
use App\Enums\FilterableResource;
use App\Http\Requests\Exporting\StoreExportRequest;
use App\Http\Resources\ExportResource;
use App\Services\Exporting\ExportResourceRegistry;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportController extends Controller
{
    public function index(Request $request, ExportRepository $exports): Response
    {
        $user = $request->user();

        abort_if($user === null, 404);

        return Inertia::render('Admin/Exports/Index', [
            'exports' => ExportResource::collection($exports->forUser($user->id)),
        ]);
    }

    public function store(StoreExportRequest $request, ExportResourceRegistry $registry, RequestExport $requestExport, TenantContext $tenantContext): RedirectResponse
    {
        $resource = FilterableResource::from((string) $request->validated('resource_type'));

        $this->authorizeExportOf($resource);

        $user = $request->user();

        abort_if($user === null, 404);

        $requestExport($request->toDto(
            userId: $user->id,
            resellerId: $registry->requiresTenantContext($resource) ? $tenantContext->id() : null,
        ));

        return back()->with('success', __('Export requested. It will be ready to download shortly.'));
    }

    public function download(int $export, Request $request, ExportRepository $exports): StreamedResponse
    {
        // The route itself requires the 'signed' middleware -- this is
        // the second, independent check: even a validly-signed link
        // must still belong to the requesting user and still be
        // downloadable (defense in depth, not redundant -- a signature
        // proves the link wasn't tampered with, not who's holding it).
        $user = $request->user();

        abort_if($user === null, 404);

        $found = $exports->findOwnById($user->id, $export);

        abort_if($found === null || ! $found->isDownloadable() || $found->disk === null || $found->path === null, 404);

        $filename = "export-{$found->resource_type->value}.{$found->format->extension()}";

        return Storage::disk($found->disk)->download($found->path, $filename, [
            'Content-Type' => $found->format->mimeType(),
        ]);
    }

    /**
     * Mirrors each filterable index's own authorization check -- an
     * export must be gated by the exact same boundary as browsing the
     * data it's exporting.
     */
    private function authorizeExportOf(FilterableResource $resource): void
    {
        match ($resource) {
            FilterableResource::Resellers, FilterableResource::Users, FilterableResource::Activity => $this->authorize('viewAny', 'App\Models\Reseller'),
            FilterableResource::Klanten, FilterableResource::Courses, FilterableResource::Assignments, FilterableResource::Attempts, FilterableResource::Invoices => $this->authorize('viewAny', 'App\Models\ResellerKlant'),
        };
    }
}
