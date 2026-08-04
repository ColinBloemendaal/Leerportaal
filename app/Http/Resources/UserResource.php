<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\Role;
use App\Models\Reseller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property ?Reseller $reseller
 * @property ?Role $platform_role
 * @property Carbon $created_at
 * @property ?CarbonImmutable $erased_at
 */
final class UserResource extends JsonResource
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
            'reseller_name' => $this->reseller?->name,
            'platform_role' => $this->platform_role?->value,
            'created_at' => $this->created_at->toIso8601String(),
            'erased_at' => $this->erased_at?->toIso8601String(),
        ];
    }
}
