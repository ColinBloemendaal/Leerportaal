<?php

declare(strict_types=1);

use App\Actions\Notifications\SendNotificationDigest;
use App\Enums\DigestFrequency;
use App\Mail\NotificationDigest;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('sends a digest of everything since the last send and stamps the new cutoff', function (): void {
    Mail::fake();
    $user = User::factory()->create(['notification_digest_frequency' => DigestFrequency::Daily]);
    $user->notify(new WelcomeNotification);

    $sent = app(SendNotificationDigest::class)($user);

    expect($sent)->toBeTrue()
        ->and($user->fresh()->notification_digest_sent_at)->not->toBeNull();
    Mail::assertQueued(NotificationDigest::class);
});

it('does not send when there is nothing new since the last digest', function (): void {
    Mail::fake();
    $user = User::factory()->create(['notification_digest_frequency' => DigestFrequency::Daily]);
    $user->notify(new WelcomeNotification);

    // Simulates "this notification was already covered by a previous
    // digest send" -- the cutoff is after the notification's created_at.
    $user->forceFill(['notification_digest_sent_at' => now()->addMinute()])->save();

    $sent = app(SendNotificationDigest::class)($user);

    expect($sent)->toBeFalse();
    Mail::assertNothingOutgoing();
});
