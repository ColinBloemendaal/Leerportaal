<?php

declare(strict_types=1);

use App\Actions\Notifications\MarkAllNotificationsRead;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('marks every unread notification for the given user as read, leaving other users untouched', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $user->notify(new WelcomeNotification);
    $user->notify(new WelcomeNotification);
    $otherUser->notify(new WelcomeNotification);

    $updated = app(MarkAllNotificationsRead::class)($user->id);

    expect($updated)->toBe(2)
        ->and($user->unreadNotifications()->count())->toBe(0)
        ->and($otherUser->unreadNotifications()->count())->toBe(1);
});
