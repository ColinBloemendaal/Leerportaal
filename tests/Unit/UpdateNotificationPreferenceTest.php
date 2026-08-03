<?php

declare(strict_types=1);

use App\Actions\Notifications\UpdateNotificationPreference;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a preference row on first toggle', function (): void {
    $user = User::factory()->create();

    $preference = app(UpdateNotificationPreference::class)($user->id, NotificationType::Assignment, NotificationChannel::Mail, false);

    expect($preference->enabled)->toBeFalse();
});

it('updates the existing row rather than creating a duplicate on a later toggle', function (): void {
    $user = User::factory()->create();
    $action = app(UpdateNotificationPreference::class);

    $action($user->id, NotificationType::Assignment, NotificationChannel::Mail, false);
    $action($user->id, NotificationType::Assignment, NotificationChannel::Mail, true);

    expect(NotificationPreference::query()->where('user_id', $user->id)->count())->toBe(1)
        ->and(NotificationPreference::query()->where('user_id', $user->id)->first()?->enabled)->toBeTrue();
});
