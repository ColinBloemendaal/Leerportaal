<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property ?string $subject
 * @property ?string $body_markdown
 */
final class ResellerMailTemplateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'subject' => $this->subject,
            'body_markdown' => $this->body_markdown,
        ];
    }
}
