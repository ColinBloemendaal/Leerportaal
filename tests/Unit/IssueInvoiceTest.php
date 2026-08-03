<?php

declare(strict_types=1);

use App\Actions\Billing\IssueInvoice;
use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('issues a draft invoice, creating a payment and stamping the result', function (): void {
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);

    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->create(['total_cents' => 4500]);

    $issued = app(IssueInvoice::class)($invoice);

    expect($issued->status)->toBe(InvoiceStatus::Issued)
        ->and($issued->mollie_payment_id)->not->toBeNull()
        ->and($issued->issued_at)->not->toBeNull()
        ->and($issued->due_at)->not->toBeNull()
        ->and($gateway->getPaymentStatus($issued->mollie_payment_id))->toBe('open');
});

it('refuses to issue an invoice that is not a draft', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create(['total_cents' => 4500]);

    expect(fn () => app(IssueInvoice::class)($invoice))->toThrow(LogicException::class);
});

it('refuses to issue an invoice with nothing billed', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->create(['total_cents' => 0]);

    expect(fn () => app(IssueInvoice::class)($invoice))->toThrow(LogicException::class);
});
