<?php

declare(strict_types=1);

namespace App\Actions\Invites;

use App\Contracts\Repositories\UserInviteRepository;
use App\DataTransferObjects\Invites\InviteUserData;
use App\Mail\UserInvited;
use App\Models\User;
use App\Models\UserInvite;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Validation\ValidationException;

final readonly class InviteUser
{
    public function __construct(
        private UserInviteRepository $invites,
        private Mailer $mailer,
    ) {}

    public function __invoke(InviteUserData $data): UserInvite
    {
        if (User::query()->where('email', $data->email)->exists()) {
            throw ValidationException::withMessages(['email' => trans('Someone with this email already has an account.')]);
        }

        if ($this->invites->hasPendingInviteForEmail($data->email)) {
            throw ValidationException::withMessages(['email' => trans('There is already a pending invite for this email.')]);
        }

        $invite = UserInvite::create([
            'resellerklant_id' => $data->resellerklantId,
            'invited_by_user_id' => $data->invitedByUserId,
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
        ]);

        $this->mailer->to($invite->email)->send(new UserInvited($invite));

        return $invite;
    }
}
