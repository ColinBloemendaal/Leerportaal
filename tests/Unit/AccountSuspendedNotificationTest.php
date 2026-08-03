<?php

declare(strict_types=1);

use App\Mail\AccountSuspended;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\AccountSuspendedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('routes through mail and database channels, and builds a mailable addressed to the recipient', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $admin = User::factory()->create(['reseller_id' => $reseller->id, 'email' => 'admin@example.test']);

    $notification = new AccountSuspendedNotification($reseller);

    expect($notification->via($admin))->toBe(['mail', 'database']);

    $mail = $notification->toMail($admin);
    expect($mail)->toBeInstanceOf(AccountSuspended::class)
        ->and($mail->to[0]['address'])->toBe('admin@example.test');
});

it('stores a typed database payload mentioning the reseller', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $admin = User::factory()->create(['reseller_id' => $reseller->id]);

    $data = (new AccountSuspendedNotification($reseller))->toDatabase($admin);

    expect($data['type'])->toBe('billing')
        ->and($data['message'])->toContain('Acme Training');
});
