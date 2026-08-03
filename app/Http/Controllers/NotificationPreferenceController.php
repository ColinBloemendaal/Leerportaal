<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notifications\UpdateNotificationPreference;
use App\Enums\NotificationChannel;
use App\Http\Requests\Notifications\UpdateNotificationPreferenceRequest;
use App\Services\Notifications\NotificationPreferenceGridService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationPreferenceController extends Controller
{
    public function edit(Request $request, NotificationPreferenceGridService $grid): Response
    {
        $user = $request->user();

        abort_if($user === null, 404);

        return Inertia::render('Settings/NotificationPreferences', [
            'preferences' => $grid->gridFor($user->id),
            'channels' => array_map(
                fn (NotificationChannel $channel): array => ['value' => $channel->value, 'label' => $channel->label()],
                NotificationChannel::cases(),
            ),
        ]);
    }

    public function update(
        UpdateNotificationPreferenceRequest $request,
        UpdateNotificationPreference $updatePreference,
    ): RedirectResponse {
        $user = $request->user();

        abort_if($user === null, 404);

        $data = $request->toDto();

        $updatePreference($user->id, $data->type, $data->channel, $data->enabled);

        return back();
    }
}
