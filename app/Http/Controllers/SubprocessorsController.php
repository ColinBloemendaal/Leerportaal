<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

/**
 * CLAUDE.md §7 (GDPR): "docs/subprocessors.md maintained and surfaced
 * in-app." Any authenticated user may view it -- it's not
 * reseller-specific or sensitive, just the platform's own current
 * sub-processor list, same document referenced by docs/dpa.md.
 */
final class SubprocessorsController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Settings/Subprocessors', [
            'document' => File::get(base_path('docs/subprocessors.md')),
        ]);
    }
}
