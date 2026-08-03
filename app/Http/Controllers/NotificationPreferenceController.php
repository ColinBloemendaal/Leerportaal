<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Notifications\UpdateNotificationDigestFrequency;
use App\Actions\Notifications\UpdateNotificationPreference;
use App\Enums\DigestFrequency;
use App\Enums\NotificationChannel;
use App\Http\Requests\Notifications\UpdateNotificationDigestFrequencyRequest;
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
            'digestFrequency' => $user->notification_digest_frequency->value,
            'digestFrequencyOptions' => array_map(
                fn (DigestFrequency $frequency): array => ['value' => $frequency->value, 'label' => $frequency->label()],
                DigestFrequency::cases(),
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

    public function updateDigestFrequency(
        UpdateNotificationDigestFrequencyRequest $request,
        UpdateNotificationDigestFrequency $updateFrequency,
    ): RedirectResponse {
        $user = $request->user();

        abort_if($user === null, 404);

        $updateFrequency($user->id, $request->toDto()->frequency);

        return back();
    }
}
