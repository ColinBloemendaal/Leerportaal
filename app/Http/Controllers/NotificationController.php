<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notifications\MarkAllNotificationsRead;
use App\Actions\Notifications\MarkNotificationRead;
use App\Contracts\Repositories\NotificationRepository;
use App\Http\Resources\NotificationIndexResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationController extends Controller
{
    public function index(Request $request, NotificationRepository $notifications): Response
    {
        $user = $request->user();

        abort_if($user === null, 404);

        return Inertia::render('Notifications/Index', [
            'notifications' => NotificationIndexResource::collection($notifications->forUser($user->id)),
        ]);
    }

    public function markRead(
        string $notification,
        Request $request,
        NotificationRepository $notifications,
        MarkNotificationRead $markRead,
    ): RedirectResponse {
        $user = $request->user();

        abort_if($user === null, 404);

        $found = $notifications->findOwnById($user->id, $notification);

        abort_if($found === null, 404);

        $markRead($found);

        return back();
    }

    public function markAllRead(Request $request, MarkAllNotificationsRead $markAllRead): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 404);

        $markAllRead($user->id);

        return back();
    }
}
