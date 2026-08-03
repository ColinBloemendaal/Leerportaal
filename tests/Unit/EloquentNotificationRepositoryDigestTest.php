<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\WelcomeNotification;
use App\Repositories\Eloquent\EloquentNotificationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns every notification when since is null', function (): void {
    $user = User::factory()->create();
    $user->notify(new WelcomeNotification);
    $user->notify(new WelcomeNotification);

    expect(app(EloquentNotificationRepository::class)->createdSince($user->id, null))->toHaveCount(2);
});

it('excludes notifications created at or before the given moment', function (): void {
    $user = User::factory()->create();
    $user->notify(new WelcomeNotification);

    $cutoff = now()->addMinute();

    expect(app(EloquentNotificationRepository::class)->createdSince($user->id, $cutoff))->toHaveCount(0);
});
