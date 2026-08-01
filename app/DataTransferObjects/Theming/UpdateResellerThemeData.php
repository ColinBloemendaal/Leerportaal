<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Theming;

use Illuminate\Http\UploadedFile;

final readonly class UpdateResellerThemeData
{
    public function __construct(
        public string $primaryColor,
        public ?string $secondaryColor,
        public ?string $accentColor,
        public ?string $fontFamily,
        public ?UploadedFile $logo,
        public ?UploadedFile $favicon,
        public ?UploadedFile $loginBackground,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            primaryColor: $data['primary_color'],
            secondaryColor: $data['secondary_color'] ?? null,
            accentColor: $data['accent_color'] ?? null,
            fontFamily: $data['font_family'] ?? null,
            logo: $data['logo'] ?? null,
            favicon: $data['favicon'] ?? null,
            loginBackground: $data['login_background'] ?? null,
        );
    }
}
