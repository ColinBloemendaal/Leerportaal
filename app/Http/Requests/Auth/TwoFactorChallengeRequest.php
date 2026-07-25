<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\TwoFactorChallengeData;
use Illuminate\Foundation\Http\FormRequest;

final class TwoFactorChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
        ];
    }

    public function toDto(): TwoFactorChallengeData
    {
        return TwoFactorChallengeData::fromArray($this->validated());
    }
}
