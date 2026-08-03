<?php

declare(strict_types=1);

namespace App\Http\Requests\Notifications;

use App\DataTransferObjects\Notifications\UpdateNotificationPreferenceData;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Self-scoped, same as ConfirmTwoFactorAuthenticationRequest -- a
 * notification preference always belongs to the acting user, there is
 * no target-user parameter to check a Policy against.
 */
final class UpdateNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(NotificationType::class)],
            'channel' => ['required', new Enum(NotificationChannel::class)],
            'enabled' => ['required', 'boolean'],
        ];
    }

    public function toDto(): UpdateNotificationPreferenceData
    {
        return new UpdateNotificationPreferenceData(
            type: NotificationType::from((string) $this->validated('type')),
            channel: NotificationChannel::from((string) $this->validated('channel')),
            enabled: (bool) $this->validated('enabled'),
        );
    }
}
