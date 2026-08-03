<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\WelcomeNotification;
use Inertia\Testing\AssertableInertia;

it('lists the authenticated user\'s own notifications', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $user->notify(new WelcomeNotification);
    $otherUser->notify(new WelcomeNotification);

    $this->actingAs($user)->get('/notifications')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Notifications/Index')
            ->has('notifications.data', 1));
});

it('marks a notification read for its own owner', function (): void {
    $user = User::factory()->create();
    $user->notify(new WelcomeNotification);
    $notification = $user->notifications()->first();

    $this->actingAs($user)->post("/notifications/{$notification->id}/read")->assertRedirect();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('404s marking read a notification that belongs to someone else', function (): void {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $owner->notify(new WelcomeNotification);
    $notification = $owner->notifications()->first();

    $this->actingAs($user)->post("/notifications/{$notification->id}/read")->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

it('marks every one of its own notifications read, leaving other users untouched', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $user->notify(new WelcomeNotification);
    $user->notify(new WelcomeNotification);
    $otherUser->notify(new WelcomeNotification);

    $this->actingAs($user)->post('/notifications/read-all')->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0)
        ->and($otherUser->unreadNotifications()->count())->toBe(1);
});

it('redirects guests to login', function (): void {
    $this->get('/notifications')->assertRedirect('/login');
});
