<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ?string $log_name
 * @property string $description
 * @property ?string $event
 * @property ?string $subject_type
 * @property ?int $subject_id
 * @property ?object $causer
 * @property Carbon $created_at
 */
final class ActivityLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'event' => $this->event,
            'subject_type' => $this->subject_type === null ? null : class_basename($this->subject_type),
            'subject_id' => $this->subject_id,
            'causer_name' => $this->causer instanceof User ? $this->causer->name : null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
