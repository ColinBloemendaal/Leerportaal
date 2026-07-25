<?php

declare(strict_types=1);

namespace App\Http\Requests\Invites;

use App\DataTransferObjects\Invites\InviteUserData;
use App\Enums\Role;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', 'App\Models\UserInvite') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'resellerklant_id' => [
                'nullable',
                'integer',
                Rule::exists('resellerklanten', 'id')->where('reseller_id', app(TenantContext::class)->id()),
            ],
            'role' => ['required', Rule::enum(Role::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $role = Role::tryFrom((string) $this->input('role'));

            if ($role === null) {
                return;
            }

            $allowed = $this->filled('resellerklant_id') ? Role::klantRoles() : Role::resellerRoles();

            if (! in_array($role, $allowed, true)) {
                $validator->errors()->add('role', trans('That role is not valid for this invite type.'));
            }
        });
    }

    public function toDto(): InviteUserData
    {
        $userId = $this->user()?->getAuthIdentifier();
        abort_unless(is_int($userId), 401);

        return InviteUserData::fromArray([
            ...$this->validated(),
            'invited_by_user_id' => $userId,
        ]);
    }
}
