<?php

declare(strict_types=1);

use App\Actions\Billing\ProcessOverdueInvoice;
use App\Actions\Billing\RetryOverdueInvoicePayment;
use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Enums\ResellerStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('retries the payment when a retry is still available', function (): void {
    $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create([
        'status' => InvoiceStatus::Overdue,
        'total_cents' => 4500,
        'dunning_attempts' => 0,
    ]);

    app(ProcessOverdueInvoice::class)($invoice);

    expect($invoice->fresh()->status)->toBe(InvoiceStatus::Issued)
        ->and($reseller->fresh()->status)->toBe(ResellerStatus::Active);
});

it('suspends the reseller once retries are exhausted', function (): void {
    $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create([
        'status' => InvoiceStatus::Overdue,
        'total_cents' => 4500,
        'dunning_attempts' => RetryOverdueInvoicePayment::MAX_ATTEMPTS,
    ]);

    app(ProcessOverdueInvoice::class)($invoice);

    expect($reseller->fresh()->status)->toBe(ResellerStatus::Suspended);
});
