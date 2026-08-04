<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Gdpr\DataSubjectExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * CLAUDE.md §8 (GDPR): "Data export per data subject: JSON + human
 * readable." Always the authenticated user's own data -- no id parameter,
 * no separate authorization check beyond being logged in, since there's
 * no "whose data" decision to make.
 */
final class DataSubjectExportController extends Controller
{
    public function show(Request $request): InertiaResponse
    {
        $user = $request->user();

        abort_if($user === null, 404);

        return Inertia::render('Settings/DataExport');
    }

    public function json(Request $request, DataSubjectExportService $export): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 404);

        return response()
            ->json($export->forUser($user), Response::HTTP_OK, [], JSON_PRETTY_PRINT)
            ->header('Content-Disposition', 'attachment; filename="my-data.json"');
    }

    public function html(Request $request, DataSubjectExportService $export): Response
    {
        $user = $request->user();

        abort_if($user === null, 404);

        $html = View::make('gdpr.data-export', ['data' => $export->forUser($user)])->render();

        return response($html)->header('Content-Disposition', 'attachment; filename="my-data.html"');
    }
}
