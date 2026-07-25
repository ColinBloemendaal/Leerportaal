<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\LoginData;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;

/**
 * Tenant-aware: on a branded domain/cookie, only that reseller's users
 * (or platform staff) may log in; on the unbranded fallback, only
 * platform staff may. See CLAUDE.md §1 tenant resolution chain.
 *
 * Verifies credentials without persisting a session (StatefulGuard::once())
 * so the caller can decide whether to complete the login immediately or
 * hold for a two-factor challenge first.
 */
final readonly class AuthenticateUser
{
    public function __construct(
        private StatefulGuard $guard,
        private TenantContext $tenantContext,
    ) {}

    public function __invoke(LoginData $data): User
    {
        if (! $this->guard->once(['email' => $data->email, 'password' => $data->password])) {
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        /** @var User $user */
        $user = $this->guard->user();

        if (! $this->belongsToCurrentContext($user)) {
            $this->guard->logout();

            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        return $user;
    }

    private function belongsToCurrentContext(User $user): bool
    {
        $tenantId = $this->tenantContext->id();

        if ($tenantId === null) {
            return $user->reseller_id === null;
        }

        return $user->reseller_id === null || $user->reseller_id === $tenantId;
    }
}
