<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\Auth\StopImpersonation;
use App\Contracts\Repositories\ImpersonationRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hard session limit per CLAUDE.md §7 -- ends the impersonation and
 * restores the impersonator automatically once config('impersonation.
 * session_minutes') elapses, regardless of activity.
 */
final class EnforceImpersonationSessionLimit
{
    public function __construct(
        private readonly ImpersonationRepository $impersonations,
        private readonly StopImpersonation $stopImpersonation,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $impersonationId = $request->session()->get('impersonation_id');
        $impersonatorId = $request->session()->get('impersonator_id');

        if (! is_int($impersonationId) || ! is_int($impersonatorId)) {
            return $next($request);
        }

        $impersonation = $this->impersonations->findActive($impersonationId);

        if ($impersonation === null) {
            return $next($request);
        }

        $limit = (int) config('impersonation.session_minutes');

        if ($impersonation->started_at->addMinutes($limit)->isPast()) {
            ($this->stopImpersonation)($impersonationId, $impersonatorId);

            return redirect('/')->with('status', __('Impersonation session expired.'));
        }

        return $next($request);
    }
}
