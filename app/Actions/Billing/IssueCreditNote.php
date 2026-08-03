<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Models\CreditNote;
use App\Models\Invoice;

/**
 * CLAUDE.md §11: invoices are immutable once issued -- this never touches
 * the invoice it corrects (no total_cents adjustment, no status change).
 * The credit note is its own standalone record of the correction; netting
 * an invoice against its credit notes is a display/reporting concern for
 * whatever later reads them (e.g. the reseller billing dashboard), not
 * something this Action mutates state for.
 */
final readonly class IssueCreditNote
{
    public function __invoke(Invoice $invoice, int $amountCents, string $reason): CreditNote
    {
        return CreditNote::query()->create([
            'reseller_id' => $invoice->reseller_id,
            'invoice_id' => $invoice->id,
            'amount_cents' => $amountCents,
            'reason' => $reason,
            'issued_at' => now(),
        ]);
    }
}
