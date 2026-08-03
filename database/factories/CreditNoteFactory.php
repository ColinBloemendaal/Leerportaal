<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditNote>
 */
final class CreditNoteFactory extends Factory
{
    protected $model = CreditNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount_cents' => fake()->numberBetween(300, 5000),
            'reason' => fake()->sentence(4),
            'issued_at' => now(),
        ];
    }
}
