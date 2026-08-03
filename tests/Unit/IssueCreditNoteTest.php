<?php

declare(strict_types=1);

use App\Actions\Billing\IssueCreditNote;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a credit note without touching the invoice it corrects', function (): void {
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create(['total_cents' => 5000]);

    $creditNote = app(IssueCreditNote::class)($invoice, 1500, 'Goodwill credit');

    expect($creditNote->invoice_id)->toBe($invoice->id)
        ->and($creditNote->reseller_id)->toBe($reseller->id)
        ->and($creditNote->amount_cents->cents)->toBe(1500)
        ->and($creditNote->reason)->toBe('Goodwill credit')
        ->and($creditNote->issued_at)->not->toBeNull()
        ->and($invoice->fresh()->total_cents->cents)->toBe(5000)
        ->and($invoice->fresh()->status)->toBe(InvoiceStatus::Issued);
});
