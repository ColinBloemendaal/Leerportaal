<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Tenancy\TenantContext;
use Illuminate\Database\ConnectionInterface;
use LogicException;

/**
 * Draft -> Issued, exactly once: creates the real Mollie payment for the
 * invoice's already-accumulated total and stamps the result on the
 * invoice. From this point on the invoice is immutable (CLAUDE.md §11) --
 * no code path may add another line to it after this runs (see
 * InvoiceStatus::isOpenForNewLines()).
 */
final readonly class IssueInvoice
{
    public function __construct(
        private PaymentGateway $paymentGateway,
        private TenantContext $tenantContext,
        private ConnectionInterface $db,
    ) {}

    public function __invoke(Invoice $invoice): Invoice
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw new LogicException("Invoice #{$invoice->id} is not a draft and cannot be issued again.");
        }

        $totalCents = $invoice->total_cents === null ? 0 : $invoice->total_cents->cents;

        if ($totalCents <= 0) {
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

        return $this->db->transaction(function () use ($invoice, $reseller, $totalCents): Invoice {
            $description = $reseller === null
                ? "Invoice #{$invoice->id}"
                : "Invoice for {$reseller->name}, {$invoice->period_start->format('F Y')}";

            // No reseller-facing invoice page exists yet (that's the
            // "reseller billing dashboard" sub-task) -- redirects to the
            // reseller admin dashboard as a placeholder destination.
            $payment = $this->paymentGateway->createPayment($totalCents, $description, route('admin.reseller.dashboard'));

            $invoice->status = InvoiceStatus::Issued;
            $invoice->mollie_payment_id = is_string($payment['id'] ?? null) ? $payment['id'] : null;
            $invoice->issued_at = now()->toImmutable();
            $invoice->due_at = now()->addDays(14)->toImmutable();
            $invoice->save();

            return $invoice;
        });
    }
}
