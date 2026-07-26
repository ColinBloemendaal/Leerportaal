<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Reseller;
use App\Models\ResellerTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResellerTheme>
 */
final class ResellerThemeFactory extends Factory
{
    protected $model = ResellerTheme::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            'primary_color' => fake()->hexColor(),
            'secondary_color' => fake()->hexColor(),
            'accent_color' => fake()->hexColor(),
            'font_family' => null,
            'logo_path' => null,
            'favicon_path' => null,
            'login_background_path' => null,
        ];
    }
}
