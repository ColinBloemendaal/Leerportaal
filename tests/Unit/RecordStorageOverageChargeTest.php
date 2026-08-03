<?php

declare(strict_types=1);

use App\Actions\Billing\RecordStorageOverageCharge;
use App\Contracts\Storage\StorageMetering;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeStorageMetering;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('does nothing and creates no invoice when the reseller is under the limit', function (): void {
    $storage = new FakeStorageMetering;
    $this->app->instance(StorageMetering::class, $storage);
    $reseller = Reseller::factory()->create();

    $result = app(RecordStorageOverageCharge::class)($reseller);

    expect($result)->toBeNull()
        ->and(Invoice::query()->withoutTenantScope()->where('reseller_id', $reseller->id)->count())->toBe(0);
});

it('creates a storage overage line and bumps the invoice total when over the limit', function (): void {
    config(['billing.storage_overage_cents_per_gb' => 150]);
    $storage = new FakeStorageMetering;
    $this->app->instance(StorageMetering::class, $storage);
    $reseller = Reseller::factory()->create();
    $storage->setUsageBytes($reseller->id, (6 * 1024 * 1024 * 1024)); // 1 GB over

    app(RecordStorageOverageCharge::class)($reseller);

    $invoice = Invoice::query()->withoutTenantScope()->where('reseller_id', $reseller->id)->first();

    expect($invoice)->not->toBeNull()
        ->and($invoice->subtotal_cents->cents)->toBe(150)
        ->and($invoice->lines()->count())->toBe(1)
        ->and($invoice->lines()->first()->course_assignment_id)->toBeNull();
});

it('updates the same line rather than duplicating it when run again with more usage', function (): void {
    config(['billing.storage_overage_cents_per_gb' => 100]);
    $storage = new FakeStorageMetering;
    $this->app->instance(StorageMetering::class, $storage);
    $reseller = Reseller::factory()->create();
    $storage->setUsageBytes($reseller->id, 6 * 1024 * 1024 * 1024); // 1 GB over

    app(RecordStorageOverageCharge::class)($reseller);

    $storage->setUsageBytes($reseller->id, 7 * 1024 * 1024 * 1024); // 2 GB over
    app(RecordStorageOverageCharge::class)($reseller);

    $invoice = Invoice::query()->withoutTenantScope()->where('reseller_id', $reseller->id)->first();

    expect($invoice->lines()->count())->toBe(1)
        ->and($invoice->subtotal_cents->cents)->toBe(200);
});

it('removes the line and credits the invoice back once usage drops under the limit again', function (): void {
    config(['billing.storage_overage_cents_per_gb' => 100]);
    $storage = new FakeStorageMetering;
    $this->app->instance(StorageMetering::class, $storage);
    $reseller = Reseller::factory()->create();
    $storage->setUsageBytes($reseller->id, 6 * 1024 * 1024 * 1024);
    app(RecordStorageOverageCharge::class)($reseller);

    $storage->setUsageBytes($reseller->id, 1 * 1024 * 1024 * 1024);
    app(RecordStorageOverageCharge::class)($reseller);

    $invoice = Invoice::query()->withoutTenantScope()->where('reseller_id', $reseller->id)->first();

    expect($invoice->subtotal_cents->cents)->toBe(0)
        ->and(InvoiceLine::query()->where('invoice_id', $invoice->id)->count())->toBe(0);
});
