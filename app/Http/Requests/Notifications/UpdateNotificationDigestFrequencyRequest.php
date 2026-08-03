<?php

declare(strict_types=1);

namespace App\Http\Requests\Notifications;

use App\DataTransferObjects\Notifications\UpdateNotificationDigestFrequencyData;
use App\Enums\DigestFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class UpdateNotificationDigestFrequencyRequest extends FormRequest
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
            'frequency' => ['required', new Enum(DigestFrequency::class)],
        ];
    }

    public function toDto(): UpdateNotificationDigestFrequencyData
    {
        return new UpdateNotificationDigestFrequencyData(
            frequency: DigestFrequency::from((string) $this->validated('frequency')),
        );
    }
}
