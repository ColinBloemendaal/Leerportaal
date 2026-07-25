<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Reseller;
use App\Models\ResellerKlant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResellerKlant>
 */
final class ResellerKlantFactory extends Factory
{
    protected $model = ResellerKlant::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reseller_id' => Reseller::factory(),
            'name' => fake()->company(),
        ];
    }
}
