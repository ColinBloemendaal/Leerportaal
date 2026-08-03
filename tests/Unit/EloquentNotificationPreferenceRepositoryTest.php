<?php

declare(strict_types=1);

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Repositories\Eloquent\EloquentNotificationPreferenceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('defaults to enabled when no preference row exists', function (): void {
    $user = User::factory()->create();

    $enabled = app(EloquentNotificationPreferenceRepository::class)
        ->isEnabled($user->id, NotificationType::Assignment, NotificationChannel::Mail);

    expect($enabled)->toBeTrue();
});

it('honors an explicit disabled preference', function (): void {
    $user = User::factory()->create();
    NotificationPreference::factory()->create([
        'user_id' => $user->id,
        'type' => NotificationType::Assignment,
        'channel' => NotificationChannel::Mail,
        'enabled' => false,
    ]);

    $enabled = app(EloquentNotificationPreferenceRepository::class)
        ->isEnabled($user->id, NotificationType::Assignment, NotificationChannel::Mail);

    expect($enabled)->toBeFalse();
});

it('lists only the given user\'s own preferences', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    NotificationPreference::factory()->create(['user_id' => $user->id]);
    NotificationPreference::factory()->create(['user_id' => $otherUser->id]);

    $preferences = app(EloquentNotificationPreferenceRepository::class)->forUser($user->id);

    expect($preferences)->toHaveCount(1);
});
