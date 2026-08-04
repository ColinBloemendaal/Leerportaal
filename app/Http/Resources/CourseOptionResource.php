<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The course-assignment picker's option shape -- deliberately thinner
 * than CourseIndexResource (no status/is_catalog/created_at), since a
 * `<select>` option has no use for them.
 *
 * @property int $id
 * @property string $title
 */
final class CourseOptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
