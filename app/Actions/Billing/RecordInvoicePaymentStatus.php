<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Tenancy\TenantContext;

/**
 * The reaction half of the Mollie webhook (see MollieWebhookController).
 * Mollie's own recommended pattern -- and the actual "verification" for
 * this webhook, since Mollie doesn't sign its webhook calls the way
 * Mailgun does (App\Services\Mail\MailgunWebhookParser): the webhook body
 * carries only a payment id, never trusted directly. This Action always
 * re-fetches the authoritative status via PaymentGateway::getPaymentStatus(),
 * an authenticated call back to Mollie using our own API key -- an
 * attacker replaying or guessing a payment id can't forge a "paid" status
 * this way, since we never read a status out of the request itself.
 *
 * Idempotent: re-processing the same webhook call (Mollie's own
 * at-least-once delivery) for an invoice already in its target state is a
 * no-op, not a duplicate side effect.
 */
final readonly class RecordInvoicePaymentStatus
{
    public function __construct(
        private PaymentGateway $paymentGateway,
        private TenantContext $tenantContext,
    ) {}

    public function __invoke(string $molliePaymentId): void
    {
        // withoutTenantScope(): Mollie's webhook call carries no ambient
        // tenant at all (a platform-context request), and which reseller
        // this payment belongs to is exactly what this lookup is for.
        $invoice = Invoice::query()->withoutTenantScope()->where('mollie_payment_id', $molliePaymentId)->first();

        if ($invoice === null) {
            return;
        }

        $reseller = $invoice->reseller()->first();

        if ($reseller !== null) {
            $this->tenantContext->set($reseller);
        }

        $status = $this->paymentGateway->getPaymentStatus($molliePaymentId);

        $nextStatus = match ($status) {
            'paid' => InvoiceStatus::Paid,
            'expired', 'canceled', 'failed' => InvoiceStatus::Overdue,
            default => null,
        };

        if ($nextStatus === null || $invoice->status === $nextStatus) {
            return;
        }

        $invoice->status = $nextStatus;

        if ($nextStatus === InvoiceStatus::Paid) {
            $invoice->paid_at = now()->toImmutable();
        }

        $invoice->save();
    }
}
