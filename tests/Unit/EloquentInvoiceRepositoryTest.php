<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Repositories\Eloquent\EloquentInvoiceRepository;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('finds the current draft invoice for a reseller', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    $draft = Invoice::factory()->for($reseller)->create(['status' => InvoiceStatus::Draft]);
    Invoice::factory()->for($reseller)->issued()->create();

    $found = (new EloquentInvoiceRepository)->currentDraftForReseller($reseller->id);

    expect($found)->not->toBeNull()
        ->and($found->is($draft))->toBeTrue();
});

it('returns null when the reseller has no draft invoice', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);

    Invoice::factory()->for($reseller)->issued()->create();

    expect((new EloquentInvoiceRepository)->currentDraftForReseller($reseller->id))->toBeNull();
});
