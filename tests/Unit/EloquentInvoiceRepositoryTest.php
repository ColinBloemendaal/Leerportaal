<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceLine;
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

it('finds drafts ready to issue across every reseller, ignoring empty or already-issued ones', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    $ready = Invoice::factory()->for($resellerA)->create([
        'period_end' => now()->subDay(),
        'subtotal_cents' => 1500,
    ]);
    // Still within its period -- not ready yet.
    Invoice::factory()->for($resellerA)->create([
        'period_end' => now()->addDay(),
        'subtotal_cents' => 1500,
    ]);
    // Period ended, but nothing was billed.
    Invoice::factory()->for($resellerB)->create([
        'period_end' => now()->subDay(),
        'subtotal_cents' => 0,
    ]);
    // Period ended and billed, but already issued.
    Invoice::factory()->for($resellerB)->issued()->create([
        'period_end' => now()->subDay(),
        'total_cents' => 1500,
    ]);

    $found = (new EloquentInvoiceRepository)->draftsReadyToIssue();

    expect($found->pluck('id')->all())->toBe([$ready->id]);
});

it('finds overdue invoices across every reseller', function (): void {
    $reseller = Reseller::factory()->create();

    $overdue = Invoice::factory()->for($reseller)->issued()->create(['status' => InvoiceStatus::Overdue]);
    Invoice::factory()->for($reseller)->issued()->create();
    Invoice::factory()->for($reseller)->paid()->create();

    $found = (new EloquentInvoiceRepository)->overdue();

    expect($found->pluck('id')->all())->toBe([$overdue->id]);
});

it('finds the current draft invoice with its lines eager-loaded', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $draft = Invoice::factory()->for($reseller)->create();
    InvoiceLine::factory()->for($draft)->create();

    $found = (new EloquentInvoiceRepository)->currentDraftForResellerWithLines($reseller->id);

    expect($found)->not->toBeNull()
        ->and($found->relationLoaded('lines'))->toBeTrue()
        ->and($found->lines)->toHaveCount(1);
});

it('lists a reseller\'s own past invoices, most recent period first, excluding drafts', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    Invoice::factory()->for($reseller)->create(); // draft, excluded
    $older = Invoice::factory()->for($reseller)->issued()->create(['period_start' => now()->subMonths(2)->startOfMonth()]);
    $newer = Invoice::factory()->for($reseller)->paid()->create(['period_start' => now()->subMonth()->startOfMonth()]);

    $found = (new EloquentInvoiceRepository)->historyForReseller($reseller->id);

    expect($found->pluck('id')->all())->toBe([$newer->id, $older->id]);
});
