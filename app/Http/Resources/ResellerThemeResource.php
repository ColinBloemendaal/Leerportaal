<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $primary_color
 * @property ?string $secondary_color
 * @property ?string $accent_color
 * @property ?string $font_family
 * @property ?string $custom_css
 * @property ?string $sender_name
 * @property ?string $reply_to_email
 * @property ?string $footer_text
 * @property ?string $support_email
 * @property ?string $terms_url
 * @property ?string $privacy_url
 */
final class ResellerThemeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'accent_color' => $this->accent_color,
            'font_family' => $this->font_family,
            'custom_css' => $this->custom_css,
            'sender_name' => $this->sender_name,
            'reply_to_email' => $this->reply_to_email,
            'footer_text' => $this->footer_text,
            'support_email' => $this->support_email,
            'terms_url' => $this->terms_url,
            'privacy_url' => $this->privacy_url,
        ];
    }
}
