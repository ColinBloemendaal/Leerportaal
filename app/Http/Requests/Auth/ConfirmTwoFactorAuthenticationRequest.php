<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\ConfirmTwoFactorAuthenticationData;
use Illuminate\Foundation\Http\FormRequest;

final class ConfirmTwoFactorAuthenticationRequest extends FormRequest
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
            'code' => ['required', 'string'],
        ];
    }

    public function toDto(): ConfirmTwoFactorAuthenticationData
    {
        return ConfirmTwoFactorAuthenticationData::fromArray($this->validated());
    }
}
