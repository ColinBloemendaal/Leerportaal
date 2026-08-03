<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\Billing\VatCalculator;
use App\Support\Money;
use App\Tenancy\TenantContext;
use Illuminate\Database\ConnectionInterface;
use LogicException;

/**
 * Draft -> Issued, exactly once: computes VAT for the invoice's
 * already-accumulated subtotal (deliberately at issue time, not while
 * still a draft -- the reseller's country/VAT ID could still change while
 * lines are still accumulating, and only the value at the moment of
 * issuing should ever be locked in), creates the real Mollie payment for
 * the resulting grand total, and stamps everything on the invoice. From
 * this point on the invoice is immutable (CLAUDE.md §11) -- no code path
 * may add another line to it after this runs (see
 * InvoiceStatus::isOpenForNewLines()).
 */
final readonly class IssueInvoice
{
    public function __construct(
        private PaymentGateway $paymentGateway,
        private VatCalculator $vatCalculator,
        private TenantContext $tenantContext,
        private ConnectionInterface $db,
    ) {}

    public function __invoke(Invoice $invoice): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw new LogicException("Invoice #{$invoice->id} is not a draft and cannot be issued again.");
        }

        $subtotalCents = $invoice->subtotal_cents === null ? 0 : $invoice->subtotal_cents->cents;

        if ($subtotalCents <= 0) {
            throw new LogicException("Invoice #{$invoice->id} has nothing billed and cannot be issued.");
        }

        $reseller = $invoice->reseller()->first();

        // Batch-issuing (App\Console\Commands\IssueDueInvoicesCommand)
        // spans every reseller in one run with no single ambient tenant --
        // TenantScope fails closed with none set, which would otherwise
        // make $invoice->save() below silently update zero rows.
        if ($reseller !== null) {
            $this->tenantContext->set($reseller);
        }

        $vat = $this->vatCalculator->calculate(
            $subtotalCents,
            $reseller?->country_code,
            $reseller?->vat_id,
        );
        $totalCents = $subtotalCents + $vat->vatCents;

        return $this->db->transaction(function () use ($invoice, $reseller, $vat, $totalCents): Invoice {
            $description = $reseller === null
                ? "Invoice #{$invoice->id}"
                : "Invoice for {$reseller->name}, {$invoice->period_start->format('F Y')}";

            // No reseller-facing invoice page exists yet (that's the
            // "reseller billing dashboard" sub-task) -- redirects to the
            // reseller admin dashboard as a placeholder destination.
            $payment = $this->paymentGateway->createPayment($totalCents, $description, route('admin.reseller.dashboard'));

            $invoice->status = InvoiceStatus::Issued;
            $invoice->mollie_payment_id = is_string($payment['id'] ?? null) ? $payment['id'] : null;
            $invoice->vat_rate_percent = max(0, $vat->ratePercent);
            $invoice->vat_cents = Money::fromCents($vat->vatCents);
            $invoice->reverse_charge = $vat->reverseCharge;
            $invoice->total_cents = Money::fromCents($totalCents);
            $invoice->issued_at = now()->toImmutable();
            $invoice->due_at = now()->addDays(14)->toImmutable();
            $invoice->save();

            return $invoice;
        });
    }
}
