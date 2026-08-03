<?php

declare(strict_types=1);

use App\Contracts\Billing\PaymentGateway;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('processes every overdue invoice and reports the count', function (): void {
    $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();
    Invoice::factory()->for($resellerA)->issued()->create(['status' => InvoiceStatus::Overdue, 'total_cents' => 1500]);
    Invoice::factory()->for($resellerB)->issued()->create(['status' => InvoiceStatus::Overdue, 'total_cents' => 2500]);
    Invoice::factory()->for($resellerA)->issued()->create();

    $this->artisan('billing:process-overdue')
        ->expectsOutput('Processed 2 overdue invoice(s).')
        ->assertExitCode(0);
});
