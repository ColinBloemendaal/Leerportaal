<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\Repositories\UserInviteRepository;
use App\DataTransferObjects\Auth\AcceptInviteData;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Validation\ValidationException;

/**
 * Assumes the caller already resolved TenantContext to the invite's own
 * reseller (the accept route carries the reseller slug for exactly this
 * reason) -- see InviteAcceptController.
 */
final readonly class AcceptInvite
{
    public function __construct(
        private UserInviteRepository $invites,
        private ConnectionInterface $db,
    ) {}

    public function __invoke(AcceptInviteData $data): User
    {
        $invite = $this->invites->findPendingById($data->inviteId);

        if ($invite === null || ! hash_equals(sha1($invite->email), $data->hash)) {
            throw ValidationException::withMessages(['password' => trans('This invite is no longer valid.')]);
        }

        if (User::query()->where('email', $invite->email)->exists()) {
            throw ValidationException::withMessages(['password' => trans('Someone with this email already has an account.')]);
        }

        return $this->db->transaction(function () use ($invite, $data): User {
            $user = User::create([
                'reseller_id' => $invite->reseller_id,
                'resellerklant_id' => $invite->resellerklant_id,
                'name' => $invite->name,
                'email' => $invite->email,
                'password' => $data->password,
                'email_verified_at' => now(),
            ]);

            $invite->forceFill(['accepted_at' => now()])->save();

            return $user;
        });
    }
}
