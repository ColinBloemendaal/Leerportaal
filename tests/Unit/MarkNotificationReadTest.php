<?php

declare(strict_types=1);

use App\Actions\Notifications\MarkNotificationRead;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('marks the given notification as read', function (): void {
    $user = User::factory()->create();
    $user->notify(new WelcomeNotification);
    $notification = $user->notifications()->first();

    expect($notification->read_at)->toBeNull();

    app(MarkNotificationRead::class)($notification);

    expect($notification->fresh()->read_at)->not->toBeNull();
});
