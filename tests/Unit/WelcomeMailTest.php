<?php

declare(strict_types=1);

use App\Mail\Welcome;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Address;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('falls back to the reseller name when there is no theme', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $user = User::factory()->create(['reseller_id' => $reseller->id, 'name' => 'Jane Doe']);

    $mail = new Welcome($user);

    expect($mail->envelope()->from)->toBeInstanceOf(Address::class)
        ->and($mail->envelope()->from->name)->toBe('Acme Training')
        ->and($mail->envelope()->subject)->toBe('Welcome to Acme Training')
        ->and($mail->content()->markdown)->toBe('emails.welcome');
});

it('uses the theme sender name when set', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    ResellerTheme::factory()->for($reseller, 'reseller')->create(['sender_name' => 'Acme Support Team']);
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $mail = new Welcome($user);

    expect($mail->envelope()->from->name)->toBe('Acme Support Team');
});

it('throws for a user with no reseller', function (): void {
    $user = User::factory()->platformStaff()->create();

    expect(fn () => new Welcome($user))->toThrow(RuntimeException::class);
});
