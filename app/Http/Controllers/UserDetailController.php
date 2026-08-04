<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\ActivityLogRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UserDetailController extends Controller
{
    public function show(int $user, Request $request, UserRepository $users, ActivityLogRepository $activity): Response
    {
        // Same access boundary as the platform users index.
        $this->authorize('viewAny', 'App\Models\Reseller');

        $found = $users->findById($user);

        abort_if($found === null, 404);

        return Inertia::render('Admin/Platform/Users/Show', [
            'user' => new UserResource($found),
            'timeline' => ActivityLogResource::collection($activity->timelineForUser($found->id)),
            // UserPolicy::impersonate() is its own, narrower check (e.g.
            // only super-admins may impersonate from the platform-wide
            // user list) -- computed here rather than baked into
            // UserResource, since it depends on who's viewing, not just
            // who's viewed.
            'canImpersonate' => $request->user()?->can('impersonate', $found) ?? false,
            'canErase' => $request->user()?->can('erase', $found) ?? false,
        ]);
    }
}
