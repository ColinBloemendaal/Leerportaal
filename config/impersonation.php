<?php

declare(strict_types=1);

return [

    /*
     * Hard session limit per CLAUDE.md §7 -- an impersonation session
     * ends automatically after this many minutes, regardless of
     * activity. Enforced by App\Http\Middleware\EnforceImpersonationSessionLimit.
     */
    'session_minutes' => (int) env('IMPERSONATION_SESSION_MINUTES', 15),

];
