<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Contracts\Repositories\InvoiceRepository;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Reseller;
use App\Services\Billing\StorageOverageCalculator;
use App\Support\Money;
use App\Tenancy\TenantContext;
use Illuminate\Database\ConnectionInterface;

/**
 * Unlike RecordBillableAssignment (one line per one-time event), storage
 * usage is ongoing -- this Action is meant to run repeatedly (daily) for
 * as long as a reseller stays over the included 5 GB, and must be
 * idempotent against that: it upserts the *one* storage-overage line on
 * the current draft invoice (matched by invoice + course_assignment_id
 * IS NULL + this fixed description) rather than adding a new line every
 * time it runs, and adjusts the invoice's total by the delta, not the
 * full new amount. If usage drops back under the included amount, the
 * line is removed and the invoice credited back -- there's nothing to
 * dispute since the invoice is still a draft at that point.
 */
final readonly class RecordStorageOverageCharge
{
    private const DESCRIPTION = 'Storage overage';

    public function __construct(
        private StorageOverageCalculator $calculator,
        private FindOrCreateDraftInvoice $findOrCreateDraftInvoice,
        private InvoiceRepository $invoices,
        private TenantContext $tenantContext,
        private ConnectionInterface $db,
    ) {}

    public function __invoke(Reseller $reseller): ?InvoiceLine
    {
        $chargeCents = $this->calculator->overageChargeCents($reseller->id);

        // Set before the very first scoped read below: TenantScope fails
        // closed with no ambient tenant, and a batch command iterates many
        // resellers in one run with no single ambient tenant of its own.
        $this->tenantContext->set($reseller);

        // No existing draft and nothing to charge: skip entirely, rather
        // than creating an empty EUR 0.00 invoice on every daily run for
        // a reseller who has never been over the limit.
        $invoice = $this->invoices->currentDraftForReseller($reseller->id);

        if ($chargeCents <= 0 && $invoice === null) {
            return null;
        }

        return $this->db->transaction(function () use ($reseller, $chargeCents, $invoice): ?InvoiceLine {
            $invoice ??= ($this->findOrCreateDraftInvoice)($reseller->id);

            $existingLine = InvoiceLine::query()
                ->where('invoice_id', $invoice->id)
                ->whereNull('course_assignment_id')
                ->where('description', self::DESCRIPTION)
                ->first();

            $previousAmountCents = $existingLine === null || $existingLine->amount_cents === null
                ? 0
                : $existingLine->amount_cents->cents;

            if ($chargeCents <= 0) {
                if ($existingLine !== null) {
                    $this->adjustInvoiceTotal($invoice, -$previousAmountCents);
                    $existingLine->delete();
                }

                return null;
            }

            if ($existingLine !== null) {
                $existingLine->amount_cents = Money::fromCents($chargeCents);
                $existingLine->save();
                $this->adjustInvoiceTotal($invoice, $chargeCents - $previousAmountCents);

                return $existingLine;
            }

            $line = InvoiceLine::query()->create([
                'invoice_id' => $invoice->id,
                'course_assignment_id' => null,
                'description' => self::DESCRIPTION,
                'amount_cents' => $chargeCents,
            ]);
            $this->adjustInvoiceTotal($invoice, $chargeCents);

            return $line;
        });
    }

    private function adjustInvoiceTotal(Invoice $invoice, int $deltaCents): void
    {
        $currentTotalCents = $invoice->total_cents === null ? 0 : $invoice->total_cents->cents;
        $invoice->total_cents = Money::fromCents(max(0, $currentTotalCents + $deltaCents));
        $invoice->save();
    }
}
