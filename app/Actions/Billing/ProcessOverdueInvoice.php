<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Models\Invoice;

/**
 * One overdue invoice, one decision: retry the payment if there's a
 * retry left (and the cooldown between attempts has passed), otherwise
 * -- retries exhausted -- suspend the reseller. RetryOverdueInvoicePayment
 * and SuspendResellerForNonPayment stay separate, reusable Actions rather
 * than being inlined here, since "decide what to do" and "do the money
 * thing" / "do the suspension thing" are genuinely different concerns.
 */
final readonly class ProcessOverdueInvoice
{
    public function __construct(
        private RetryOverdueInvoicePayment $retryPayment,
        private SuspendResellerForNonPayment $suspendReseller,
    ) {}

    public function __invoke(Invoice $invoice): void
    {
        $retried = ($this->retryPayment)($invoice);

        if ($retried) {
            return;
        }

        if ($invoice->dunning_attempts >= RetryOverdueInvoicePayment::MAX_ATTEMPTS) {
            ($this->suspendReseller)($invoice);
        }
    }
}
