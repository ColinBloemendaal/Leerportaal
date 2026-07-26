<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Impersonation;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;

final readonly class StopImpersonation
{
    public function __construct(private StatefulGuard $guard) {}

    public function __invoke(int $impersonationId, int $impersonatorUserId): void
    {
        // Instance update (not a query builder update) so HasAuditLog's
        // model-event boot hook actually fires and logs this.
        Impersonation::query()->findOrFail($impersonationId)->update(['ended_at' => now()]);

        $this->guard->login(User::query()->findOrFail($impersonatorUserId));

        session()->forget(['impersonation_id', 'impersonator_id', 'impersonator_name', 'impersonated_name']);
    }
}
