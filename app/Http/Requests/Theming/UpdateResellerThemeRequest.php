<?php

declare(strict_types=1);

namespace App\Http\Requests\Theming;

use App\DataTransferObjects\Theming\UpdateResellerThemeData;
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
        ];
    }

    public function toDto(): UpdateResellerThemeData
    {
        return UpdateResellerThemeData::fromArray($this->validated());
    }
}
