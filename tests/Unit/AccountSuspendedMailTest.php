<?php

declare(strict_types=1);

use App\Mail\AccountSuspended;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('falls back to the reseller name when there is no theme', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);

    $mail = new AccountSuspended($reseller);

    expect($mail->envelope()->from->name)->toBe('Acme Training')
        ->and($mail->envelope()->subject)->toBe('Your account has been suspended')
        ->and($mail->content()->markdown)->toBe('emails.billing.account-suspended')
        ->and($mail->content()->with['resellerName'])->toBe('Acme Training');
});

it('uses the theme sender name when set', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    ResellerTheme::factory()->for($reseller, 'reseller')->create(['sender_name' => 'Acme Support Team']);

    $mail = new AccountSuspended($reseller);

    expect($mail->envelope()->from->name)->toBe('Acme Support Team');
});

it('renders without error', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);

    expect((new AccountSuspended($reseller))->render())->toContain('Acme Training');
});
