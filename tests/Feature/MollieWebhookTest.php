<?php

declare(strict_types=1);

use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use Tests\Fakes\FakePaymentGateway;

it('marks the matching invoice paid for a valid payment id', function (): void {
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);

    $reseller = Reseller::factory()->create();
    $payment = $gateway->createPayment(4500, 'Invoice', 'https://example.test/return');
    $invoice = Invoice::factory()->for($reseller)->issued()->create([
        'total_cents' => 4500,
        'mollie_payment_id' => $payment['id'],
    ]);
    $gateway->markAsPaid($payment['id']);

    $this->postJson('/webhooks/mollie', ['id' => $payment['id']])->assertOk();

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Paid);
});

it('rejects a request with no payment id', function (): void {
    $this->postJson('/webhooks/mollie', [])->assertStatus(422);
});

it('acknowledges an unrecognized payment id without error', function (): void {
    $this->app->instance(PaymentGateway::class, new FakePaymentGateway);

    $this->postJson('/webhooks/mollie', ['id' => 'tr_unknown'])->assertOk();
});
