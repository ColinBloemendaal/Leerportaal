<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Invites\InviteUser;
use App\Actions\Invites\RevokeInvite;
use App\Contracts\Repositories\ResellerKlantRepository;
use App\Contracts\Repositories\UserInviteRepository;
use App\Enums\Role;
use App\Http\Requests\Invites\StoreInviteRequest;
use App\Http\Resources\ResellerKlantResource;
use App\Http\Resources\UserInviteResource;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class InvitesController extends Controller
{
    public function index(UserInviteRepository $invites, ResellerKlantRepository $resellerKlanten): Response
    {
        return Inertia::render('Invites/Index', [
            'invites' => UserInviteResource::collection($invites->pendingForCurrentReseller()),
            'klanten' => ResellerKlantResource::collection($resellerKlanten->paginate(perPage: 1000)),
            'klantRoles' => $this->roleOptions(Role::klantRoles()),
            'resellerRoles' => $this->roleOptions(Role::resellerRoles()),
        ]);
    }

    /**
     * @param  list<Role>  $roles
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(array $roles): array
    {
        return array_map(
            fn (Role $role): array => ['value' => $role->value, 'label' => $role->label()],
            $roles,
        );
    }

    public function store(StoreInviteRequest $request, InviteUser $inviteUser): RedirectResponse
    {
        $inviteUser($request->toDto());

        return to_route('invites.index')->with('success', __('Invite sent.'));
    }

    public function destroy(int $invite, UserInviteRepository $invites, RevokeInvite $revokeInvite): RedirectResponse
    {
        $userInvite = $invites->findPendingById($invite);

        abort_if($userInvite === null, 404);

        $this->authorize('delete', $userInvite);

        $revokeInvite($userInvite);

        return to_route('invites.index')->with('success', __('Invite revoked.'));
    }
}
