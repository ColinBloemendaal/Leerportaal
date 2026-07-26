<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\StartImpersonationData;
use App\Models\Impersonation;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;

final readonly class StartImpersonation
{
    public function __construct(private StatefulGuard $guard) {}

    public function __invoke(StartImpersonationData $data): Impersonation
    {
        $impersonator = User::query()->findOrFail($data->impersonatorUserId);
        $target = User::query()->findOrFail($data->targetUserId);

        $impersonation = Impersonation::create([
            'impersonator_user_id' => $impersonator->id,
            'impersonated_user_id' => $target->id,
            'reason' => $data->reason,
            'started_at' => now(),
        ]);

        // Read by the impersonation banner (Inertia shared prop) and the
        // session-limit middleware without either needing to touch the
        // Model layer -- see CLAUDE.md §3a.
        session([
            'impersonation_id' => $impersonation->id,
            'impersonator_id' => $impersonator->id,
            'impersonator_name' => $impersonator->name,
            'impersonated_name' => $target->name,
        ]);

        $this->guard->login($target);

        return $impersonation;
    }
}
