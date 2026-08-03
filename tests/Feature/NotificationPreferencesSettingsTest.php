<?php

declare(strict_types=1);

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the full type x channel grid, defaulting every cell to enabled', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/settings/notifications')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Settings/NotificationPreferences')
            ->has('preferences', 6)
            ->has('channels', 2)
            ->where('preferences.0.channels.mail', true)
            ->where('preferences.0.channels.database', true));
});

it('reflects a stored override in the grid', function (): void {
    $user = User::factory()->create();
    NotificationPreference::factory()->create([
        'user_id' => $user->id,
        'type' => NotificationType::Assignment,
        'channel' => NotificationChannel::Mail,
        'enabled' => false,
    ]);

    $this->actingAs($user)->get('/settings/notifications')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('preferences.1.type', NotificationType::Assignment->value)
            ->where('preferences.1.channels.mail', false)
            ->where('preferences.1.channels.database', true));
});

it('updates a preference for the acting user', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/notifications', [
        'type' => NotificationType::Assignment->value,
        'channel' => NotificationChannel::Mail->value,
        'enabled' => false,
    ])->assertRedirect();

    $stored = NotificationPreference::query()->where('user_id', $user->id)->first();
    expect($stored?->enabled)->toBeFalse();
});

it('redirects guests to login', function (): void {
    $this->get('/settings/notifications')->assertRedirect('/login');
});
