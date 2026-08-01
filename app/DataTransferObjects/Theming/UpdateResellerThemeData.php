<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Theming;

final readonly class UpdateResellerThemeData
{
    public function __construct(
        public string $primaryColor,
        public ?string $secondaryColor,
        public ?string $accentColor,
        public ?string $fontFamily,
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
        );
    }
}
