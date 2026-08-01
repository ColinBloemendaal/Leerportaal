<?php

declare(strict_types=1);

use App\Mail\UserInvited;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use App\Models\UserInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Address;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('falls back to the reseller name and no reply-to when there is no theme', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    $invite = UserInvite::factory()->for($reseller, 'reseller')->create();

    $envelope = (new UserInvited($invite))->envelope();

    expect($envelope->from)->toBeInstanceOf(Address::class)
        ->and($envelope->from->address)->toBe(config('mail.from.address'))
        ->and($envelope->from->name)->toBe('Acme Training')
        ->and($envelope->replyTo)->toBe([]);
});

it('uses the theme sender name and reply-to when set', function (): void {
    $reseller = Reseller::factory()->create(['name' => 'Acme Training']);
    ResellerTheme::factory()->for($reseller, 'reseller')->create([
        'sender_name' => 'Acme Support Team',
        'reply_to_email' => 'support@acme.example',
    ]);
    $invite = UserInvite::factory()->for($reseller, 'reseller')->create();

    $envelope = (new UserInvited($invite))->envelope();

    expect($envelope->from->name)->toBe('Acme Support Team')
        ->and($envelope->from->address)->toBe(config('mail.from.address'))
        ->and($envelope->replyTo)->toHaveCount(1)
        ->and($envelope->replyTo[0]->address)->toBe('support@acme.example');
});

it('never sends from a reseller-supplied address, only the platform one', function (): void {
    $reseller = Reseller::factory()->create();
    ResellerTheme::factory()->for($reseller, 'reseller')->create(['sender_name' => 'Acme']);
    $invite = UserInvite::factory()->for($reseller, 'reseller')->create();

    $envelope = (new UserInvited($invite))->envelope();

    expect($envelope->from->address)->toBe(config('mail.from.address'));
});
