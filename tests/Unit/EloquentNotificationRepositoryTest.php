<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Repositories\Eloquent\EloquentNotificationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('paginates notifications for the given user only, newest first', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $user->notify(new WelcomeNotification);
    $user->notify(new WelcomeNotification);
    $otherUser->notify(new WelcomeNotification);

    $repository = app(EloquentNotificationRepository::class);

    expect($repository->forUser($user->id)->total())->toBe(2);
});

it('counts only unread notifications for the given user', function (): void {
    $user = User::factory()->create();
    $user->notify(new WelcomeNotification);
    $user->notify(new WelcomeNotification);

    $repository = app(EloquentNotificationRepository::class);
    expect($repository->unreadCountForUser($user->id))->toBe(2);

    $user->notifications()->first()->markAsRead();

    expect($repository->unreadCountForUser($user->id))->toBe(1);
});

it('finds a notification only if it belongs to the given user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $user->notify(new WelcomeNotification);
    $notification = $user->notifications()->first();

    $repository = app(EloquentNotificationRepository::class);

    expect($repository->findOwnById($user->id, $notification->id)?->id)->toBe($notification->id)
        ->and($repository->findOwnById($otherUser->id, $notification->id))->toBeNull();
});
