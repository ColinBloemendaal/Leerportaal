<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Reseller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('issues every due invoice and reports the count', function (): void {
    $reseller = Reseller::factory()->create();
    Invoice::factory()->for($reseller)->create(['period_end' => now()->subDay(), 'subtotal_cents' => 1500]);
    Invoice::factory()->for($reseller)->create(['period_end' => now()->subDay(), 'subtotal_cents' => 2500]);
    // Not ready: still within its period.
    Invoice::factory()->for($reseller)->create(['period_end' => now()->addDay(), 'subtotal_cents' => 1500]);

    $this->artisan('billing:issue-invoices')
        ->expectsOutput('Issued 2 invoice(s).')
        ->assertExitCode(0);

    expect(Invoice::query()->where('status', InvoiceStatus::Issued)->count())->toBe(2);
});
