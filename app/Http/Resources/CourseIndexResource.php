<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\CourseStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property CourseStatus $status
 * @property ?int $reseller_id
 * @property Carbon $created_at
 */
final class CourseIndexResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status->value,
            'is_catalog' => $this->reseller_id === null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
