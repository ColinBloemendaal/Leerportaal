<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\AcceptInviteData;
use Illuminate\Foundation\Http\FormRequest;

final class AcceptInviteRequest extends FormRequest
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
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ];
    }

    public function toDto(): AcceptInviteData
    {
        return AcceptInviteData::fromArray([
            'invite_id' => (int) $this->route('invite'),
            'hash' => (string) $this->route('hash'),
            'password' => $this->validated()['password'],
        ]);
    }
}
