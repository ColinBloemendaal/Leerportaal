<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\DataTransferObjects\Auth\StartImpersonationData;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The real authorization check (App\Policies\UserPolicy::impersonate)
 * needs the target User instance, which this class can't touch -- done
 * in the controller instead, after resolving the target via
 * UserRepository. See CLAUDE.md §3a.
 */
final class StartImpersonationRequest extends FormRequest
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
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    /**
     * Takes explicit ids rather than reading them off the request: the
     * impersonator is the current actor (identity, not user input) and
     * the target comes from the route -- neither belongs in rules().
     */
    public function toDto(int $impersonatorUserId, int $targetUserId): StartImpersonationData
    {
        return StartImpersonationData::fromArray([
            ...$this->validated(),
            'impersonator_user_id' => $impersonatorUserId,
            'target_user_id' => $targetUserId,
        ]);
    }
}
