<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Tenancy\TenantContext;

/**
 * CLAUDE.md §11 doesn't specify exact dunning thresholds -- these are a
 * reasonable engineering default, not a legal/contractual fact: retry up
 * to 3 times, at least 3 days apart. Past that, ProcessOverdueInvoice
 * hands off to SuspendResellerForNonPayment instead of calling this again.
 */
final readonly class RetryOverdueInvoicePayment
{
    public const MAX_ATTEMPTS = 3;

    private const RETRY_COOLDOWN_DAYS = 3;

    public function __construct(
        private PaymentGateway $paymentGateway,
        private TenantContext $tenantContext,
    ) {}

    /**
     * Returns true if a new payment attempt was actually created.
     */
    public function __invoke(Invoice $invoice): bool
    {
        if ($invoice->status !== InvoiceStatus::Overdue) {
            return false;
        }

        if ($invoice->dunning_attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        if ($invoice->last_dunning_attempt_at !== null
            && now()->lessThan($invoice->last_dunning_attempt_at->addDays(self::RETRY_COOLDOWN_DAYS))) {
            return false;
        }

        $reseller = $invoice->reseller()->first();

        // Batch dunning (App\Console\Commands\ProcessOverdueInvoicesCommand)
        // spans every reseller in one run with no single ambient tenant --
        // TenantScope fails closed with none set, which would otherwise
        // make $invoice->save() below silently update zero rows.
        if ($reseller !== null) {
            $this->tenantContext->set($reseller);
        }

        $totalCents = $invoice->total_cents === null ? 0 : $invoice->total_cents->cents;
        $description = $reseller === null
            ? "Invoice #{$invoice->id}"
            : "Invoice for {$reseller->name}, {$invoice->period_start->format('F Y')}";

        $payment = $this->paymentGateway->createPayment($totalCents, $description, route('admin.reseller.dashboard'));

        $invoice->status = InvoiceStatus::Issued;
        $invoice->mollie_payment_id = is_string($payment['id'] ?? null) ? $payment['id'] : null;
        $invoice->dunning_attempts += 1;
        $invoice->last_dunning_attempt_at = now()->toImmutable();
        $invoice->due_at = now()->addDays(7)->toImmutable();
        $invoice->save();

        return true;
    }
}
