<?php

declare(strict_types=1);

use App\Actions\Billing\RetryOverdueInvoicePayment;
use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a new payment attempt for an overdue invoice within the retry limit', function (): void {
    $gateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $gateway);
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create([
        'status' => InvoiceStatus::Overdue,
        'total_cents' => 4500,
        'dunning_attempts' => 0,
    ]);

    $retried = app(RetryOverdueInvoicePayment::class)($invoice);

    expect($retried)->toBeTrue()
        ->and($invoice->fresh()->status)->toBe(InvoiceStatus::Issued)
        ->and($invoice->fresh()->dunning_attempts)->toBe(1)
        ->and($invoice->fresh()->mollie_payment_id)->not->toBeNull()
        ->and($invoice->fresh()->last_dunning_attempt_at)->not->toBeNull();
});

it('does not retry an invoice that is not overdue', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create(['total_cents' => 4500]);

    expect(app(RetryOverdueInvoicePayment::class)($invoice))->toBeFalse();
});

it('does not retry once the attempt limit is reached', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create([
        'status' => InvoiceStatus::Overdue,
        'total_cents' => 4500,
        'dunning_attempts' => RetryOverdueInvoicePayment::MAX_ATTEMPTS,
    ]);

    expect(app(RetryOverdueInvoicePayment::class)($invoice))->toBeFalse();
});

it('does not retry again before the cooldown between attempts has passed', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create([
        'status' => InvoiceStatus::Overdue,
        'total_cents' => 4500,
        'dunning_attempts' => 1,
        'last_dunning_attempt_at' => now()->subDay(),
    ]);

    expect(app(RetryOverdueInvoicePayment::class)($invoice))->toBeFalse();
});
