<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Certificate $resource
 */
final class CertificateVerificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $assignment = $this->resource->courseAssignment;

        return [
            'recipient_name' => $assignment?->user?->name,
            'course_title' => $assignment?->course?->title,
            'issued_at' => $this->resource->issued_at->toFormattedDateString(),
            'verification_code' => $this->resource->verification_code,
        ];
    }
}
