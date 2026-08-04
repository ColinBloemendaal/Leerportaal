<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The course-assignment picker's cursist option shape -- id/name only,
 * never email or any other attribute the picker doesn't need.
 *
 * @property int $id
 * @property string $name
 */
final class CursistOptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
