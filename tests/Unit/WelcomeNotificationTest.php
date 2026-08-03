<?php

declare(strict_types=1);

use App\Enums\NotificationType;
use App\Mail\Welcome;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('routes through mail and database channels', function (): void {
    $user = User::factory()->create();

    expect((new WelcomeNotification)->via($user))->toBe(['mail', 'database']);
});

it('builds the Welcome mailable for the notified user', function (): void {
    $reseller = Reseller::factory()->create();
    $user = User::factory()->create(['reseller_id' => $reseller->id]);

    $mail = (new WelcomeNotification)->toMail($user);

    expect($mail)->toBeInstanceOf(Welcome::class)
        ->and($mail->user->is($user))->toBeTrue();
});

it('stores a typed database payload', function (): void {
    $user = User::factory()->create();

    $payload = (new WelcomeNotification)->toDatabase($user);

    expect($payload['type'])->toBe(NotificationType::Welcome->value)
        ->and($payload['message'])->toBeString();
});
