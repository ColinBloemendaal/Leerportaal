<?php

declare(strict_types=1);

use App\Actions\Billing\RecordInvoicePaymentStatus;
use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function issuedInvoiceWithGatewayPayment(FakePaymentGateway $gateway, int $cents = 4500): Invoice
{
    $reseller = Reseller::factory()->create();
    $payment = $gateway->createPayment($cents, 'Invoice', 'https://example.test/return');

    return Invoice::factory()->for($reseller)->issued()->create([
        'total_cents' => $cents,
        'mollie_payment_id' => $payment['id'],
    ]);
}

it('marks the invoice paid when Mollie reports it paid', function (): void {
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);
    $invoice = issuedInvoiceWithGatewayPayment($gateway);
    $gateway->markAsPaid($invoice->mollie_payment_id);

    app(RecordInvoicePaymentStatus::class)($invoice->mollie_payment_id);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->fresh()->paid_at)->not->toBeNull();
});

it('marks the invoice overdue when Mollie reports the payment failed', function (): void {
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);
    $invoice = issuedInvoiceWithGatewayPayment($gateway);
    $gateway->markAsFailed($invoice->mollie_payment_id);

    app(RecordInvoicePaymentStatus::class)($invoice->mollie_payment_id);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Overdue);
});

it('does nothing for a payment id that matches no invoice', function (): void {
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);

    // Does not throw, does not touch anything -- just a no-op.
    app(RecordInvoicePaymentStatus::class)('does-not-exist');

    expect(Invoice::query()->withoutTenantScope()->count())->toBe(0);
});

it('is idempotent: re-processing an already-paid invoice does not throw or double-stamp', function (): void {
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);
    $invoice = issuedInvoiceWithGatewayPayment($gateway);
    $gateway->markAsPaid($invoice->mollie_payment_id);

    app(RecordInvoicePaymentStatus::class)($invoice->mollie_payment_id);
    $firstPaidAt = $invoice->fresh()->paid_at;

    app(RecordInvoicePaymentStatus::class)($invoice->mollie_payment_id);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid)
        ->and($invoice->fresh()->paid_at->equalTo($firstPaidAt))->toBeTrue();
});

it('leaves the invoice alone while the payment is still open', function (): void {
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);
    $invoice = issuedInvoiceWithGatewayPayment($gateway);

    app(RecordInvoicePaymentStatus::class)($invoice->mollie_payment_id);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Issued);
});
