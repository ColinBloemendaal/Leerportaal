<?php

declare(strict_types=1);

use App\Actions\Gdpr\PurgeStaleNotifications;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config(['gdpr.retention.notifications_days' => 30]);
});

it('deletes only notifications older than the retention window for that reseller\'s users', function (): void {
    $reseller = Reseller::factory()->create();
    $otherReseller = Reseller::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    $otherCursist = User::factory()->create(['reseller_id' => $otherReseller->id]);

    $cursist->notify(new WelcomeNotification);
    $cursist->notifications()->update(['created_at' => now()->subDays(60)]);

    $recent = new WelcomeNotification;
    $cursist->notify($recent);

    $otherCursist->notify(new WelcomeNotification);
    $otherCursist->notifications()->update(['created_at' => now()->subDays(60)]);

    $deleted = app(PurgeStaleNotifications::class)($reseller);

    expect($deleted)->toBe(1)
        ->and($cursist->notifications()->count())->toBe(1)
        ->and($otherCursist->notifications()->count())->toBe(1);
});

it('purges platform staff notifications when given a null reseller', function (): void {
    $staff = User::factory()->platformStaff()->create();
    $staff->notify(new AdminAlertNotification('Test', 'Test message'));
    $staff->notifications()->update(['created_at' => now()->subDays(60)]);

    $deleted = app(PurgeStaleNotifications::class)(null);

    expect($deleted)->toBe(1);
});
