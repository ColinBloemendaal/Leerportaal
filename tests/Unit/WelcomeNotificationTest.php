<?php

declare(strict_types=1);

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Mail\Welcome;
use App\Models\NotificationPreference;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('routes through mail and database channels by default', function (): void {
    $user = User::factory()->create();

    expect((new WelcomeNotification)->via($user))->toBe(['mail', 'database']);
});

it('excludes a channel the user has disabled for this notification type', function (): void {
    $user = User::factory()->create();
    NotificationPreference::factory()->create([
        'user_id' => $user->id,
        'type' => NotificationType::Welcome,
        'channel' => NotificationChannel::Mail,
        'enabled' => false,
    ]);

    expect((new WelcomeNotification)->via($user))->toBe(['database']);
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
