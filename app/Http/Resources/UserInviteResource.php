<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Role $role
 * @property ?int $resellerklant_id
 * @property Carbon $created_at
 */
final class UserInviteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'roleLabel' => $this->role->label(),
            'isKlantInvite' => $this->resellerklant_id !== null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
