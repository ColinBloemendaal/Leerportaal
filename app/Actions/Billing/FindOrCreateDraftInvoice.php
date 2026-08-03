<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Contracts\Repositories\InvoiceRepository;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;

/**
 * Shared by every kind of charge that needs "the reseller's current-period
 * draft invoice, creating it if this is the first charge of the period" --
 * originally inlined in RecordBillableAssignment, extracted once a second
 * caller (storage overage) needed the identical logic.
 */
final readonly class FindOrCreateDraftInvoice
{
    public function __construct(private InvoiceRepository $invoices) {}

    public function __invoke(int $resellerId): Invoice
    {
        $existing = $this->invoices->currentDraftForReseller($resellerId);

        if ($existing !== null) {
            return $existing;
        }

        $periodStart = now()->startOfMonth();

        return Invoice::query()->create([
            'reseller_id' => $resellerId,
            'status' => InvoiceStatus::Draft,
            'period_start' => $periodStart,
            'period_end' => $periodStart->copy()->endOfMonth(),
            'total_cents' => 0,
        ]);
    }
}
