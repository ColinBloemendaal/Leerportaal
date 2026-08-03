<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CourseAssignment;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceLine>
 */
final class InvoiceLineFactory extends Factory
{
    protected $model = InvoiceLine::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'course_assignment_id' => CourseAssignment::factory(),
            'description' => fake()->sentence(3),
            'amount_cents' => fake()->numberBetween(300, 10000),
        ];
    }
}
