<?php

declare(strict_types=1);

use App\Actions\Billing\SuspendResellerForNonPayment;
use App\Enums\InvoiceStatus;
use App\Enums\ResellerStatus;
use App\Enums\Role;
use App\Models\Invoice;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\AccountSuspendedNotification;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('suspends the reseller and notifies its admins', function (): void {
    Notification::fake();
    $reseller = Reseller::factory()->create();
    app(TenantContext::class)->set($reseller);
    $admin = User::factory()->create(['reseller_id' => $reseller->id]);
    $admin->assignRole(Role::ResellerAdmin->value);
    $invoice = Invoice::factory()->for($reseller)->issued()->create(['status' => InvoiceStatus::Overdue]);

    $suspended = app(SuspendResellerForNonPayment::class)($invoice);

    expect($suspended)->toBeTrue()
        ->and($reseller->fresh()->status)->toBe(ResellerStatus::Suspended);

    Notification::assertSentTo($admin, AccountSuspendedNotification::class);
});

it('does nothing when the reseller is already suspended', function (): void {
    Notification::fake();
    $reseller = Reseller::factory()->suspended()->create();
    app(TenantContext::class)->set($reseller);
    $invoice = Invoice::factory()->for($reseller)->issued()->create(['status' => InvoiceStatus::Overdue]);

    $suspended = app(SuspendResellerForNonPayment::class)($invoice);

    expect($suspended)->toBeFalse();
    Notification::assertNothingSent();
});
