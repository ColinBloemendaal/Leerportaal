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

it('finds drafts ready to issue across every reseller, ignoring empty or already-issued ones', function (): void {
    $resellerA = Reseller::factory()->create();
    $resellerB = Reseller::factory()->create();

    $ready = Invoice::factory()->for($resellerA)->create([
        'period_end' => now()->subDay(),
        'total_cents' => 1500,
    ]);
    // Still within its period -- not ready yet.
    Invoice::factory()->for($resellerA)->create([
        'period_end' => now()->addDay(),
        'total_cents' => 1500,
    ]);
    // Period ended, but nothing was billed.
    Invoice::factory()->for($resellerB)->create([
        'period_end' => now()->subDay(),
        'total_cents' => 0,
    ]);
    // Period ended and billed, but already issued.
    Invoice::factory()->for($resellerB)->issued()->create([
        'period_end' => now()->subDay(),
        'total_cents' => 1500,
    ]);

    $found = (new EloquentInvoiceRepository)->draftsReadyToIssue();

    expect($found->pluck('id')->all())->toBe([$ready->id]);
});
