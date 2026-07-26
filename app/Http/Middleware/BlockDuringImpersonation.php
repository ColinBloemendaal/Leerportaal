<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Per CLAUDE.md §7: password changes, billing actions, and permission
 * changes must never be allowed while impersonating. Not attached to any
 * route yet -- none of those three areas has a route in this phase
 * (self-service password change, billing, and permission-management UI
 * are all later phases). Attach this to their routes once they exist,
 * e.g. Route::post(...)->middleware('block-during-impersonation').
 */
final class BlockDuringImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->session()->has('impersonation_id'), 403, __('This action is not available while impersonating.'));

        return $next($request);
    }
}
