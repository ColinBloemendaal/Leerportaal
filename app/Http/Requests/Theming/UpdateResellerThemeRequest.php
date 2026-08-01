<?php

declare(strict_types=1);

namespace App\Http\Requests\Theming;

use App\DataTransferObjects\Theming\UpdateResellerThemeData;
use App\Rules\SafeCustomCss;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateResellerThemeRequest extends FormRequest
{
    private const HEX_COLOR = '/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/';

    public function authorize(): bool
    {
        return $this->user()?->can('update', 'App\Models\ResellerTheme') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'primary_color' => ['required', 'string', 'regex:'.self::HEX_COLOR],
            'secondary_color' => ['nullable', 'string', 'regex:'.self::HEX_COLOR],
            'accent_color' => ['nullable', 'string', 'regex:'.self::HEX_COLOR],
            'font_family' => ['nullable', 'string', 'max:255'],

            // Type is validated by content (Laravel's `image`/`mimes` rules
            // sniff actual file signatures, not the filename extension) --
            // see CLAUDE.md §7. SVG is deliberately not accepted: its
            // dimensions aren't reliably introspectable, which would make
            // the dimensions rule either unreliably reject valid files or
            // not actually check anything.
            'logo' => [
                'nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:2048',
                'dimensions:min_width=32,min_height=32,max_width=2000,max_height=2000',
            ],
            'favicon' => ['nullable', 'file', 'mimes:png,ico', 'max:512'],
            'login_background' => [
                'nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:5120',
                'dimensions:min_width=800,min_height=600,max_width=4000,max_height=4000',
            ],

            // Hard character limit + denylist of the specific vectors that
            // matter for CSS rendered inside a raw <style> block -- see
            // App\Rules\SafeCustomCss.
            'custom_css' => ['nullable', 'string', 'max:10000', new SafeCustomCss],

            // The underlying "from" address always stays the platform's
            // own verified address (see App\Mail\Concerns\HasResellerBranding)
            // -- only the display name and reply-to are reseller-configurable.
            'sender_name' => ['nullable', 'string', 'max:255'],
            'reply_to_email' => ['nullable', 'string', 'email:rfc', 'max:255'],
        ];
    }

    public function toDto(): UpdateResellerThemeData
    {
        return UpdateResellerThemeData::fromArray($this->validated());
    }
}
