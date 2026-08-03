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

it('issues a draft invoice, charging VAT for a domestic reseller, and stamps the result', function (): void {
    config(['billing.platform_country_code' => 'NL', 'billing.vat_rate_percent' => 21]);
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);

    $reseller = Reseller::factory()->create(['name' => 'Acme Training', 'country_code' => 'NL']);
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->create(['subtotal_cents' => 4500]);

    $issued = app(IssueInvoice::class)($invoice);

    expect($issued->status)->toBe(InvoiceStatus::Issued)
        ->and($issued->mollie_payment_id)->not->toBeNull()
        ->and($issued->issued_at)->not->toBeNull()
        ->and($issued->due_at)->not->toBeNull()
        ->and($issued->vat_rate_percent)->toBe(21)
        ->and($issued->vat_cents->cents)->toBe(945)
        ->and($issued->reverse_charge)->toBeFalse()
        ->and($issued->total_cents->cents)->toBe(5445)
        ->and($gateway->getPaymentStatus($issued->mollie_payment_id))->toBe('open');
});

it('reverse-charges VAT for another EU reseller with a valid VAT ID, charging exactly the subtotal', function (): void {
    config(['billing.platform_country_code' => 'NL', 'billing.vat_rate_percent' => 21]);
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);

    $reseller = Reseller::factory()->create(['country_code' => 'DE', 'vat_id' => 'DE123456789']);
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->create(['subtotal_cents' => 4500]);

    $issued = app(IssueInvoice::class)($invoice);

    expect($issued->vat_rate_percent)->toBe(0)
        ->and($issued->vat_cents->cents)->toBe(0)
        ->and($issued->reverse_charge)->toBeTrue()
        ->and($issued->total_cents->cents)->toBe(4500);
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
    $invoice = Invoice::factory()->for($reseller)->create(['subtotal_cents' => 0]);

    expect(fn () => app(IssueInvoice::class)($invoice))->toThrow(LogicException::class);
});
