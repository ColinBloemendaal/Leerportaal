<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
final class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->startOfMonth();

        return [
            'reseller_id' => Reseller::factory(),
            'status' => InvoiceStatus::Draft,
            'period_start' => $start,
            'period_end' => $start->copy()->endOfMonth(),
            'total_cents' => 0,
        ];
    }

    public function issued(): self
    {
        return $this->state(fn (): array => [
            'status' => InvoiceStatus::Issued,
            'issued_at' => now(),
            'due_at' => now()->addDays(14),
        ]);
    }

    public function paid(): self
    {
        return $this->issued()->state(fn (): array => [
            'status' => InvoiceStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
