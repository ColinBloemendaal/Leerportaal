<?php

declare(strict_types=1);

use App\Models\Export;
use App\Models\Reseller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('purges every data type across every reseller and reports the counts', function (): void {
    config([
        'gdpr.retention.notifications_days' => 30,
        'gdpr.retention.expired_exports_grace_days' => 7,
        'gdpr.retention.activity_log_days' => 730,
    ]);

    $reseller = Reseller::factory()->create();
    $cursist = User::factory()->create(['reseller_id' => $reseller->id]);
    $cursist->notify(new WelcomeNotification);
    $cursist->notifications()->update(['created_at' => now()->subDays(60)]);

    Export::factory()->for($reseller)->create(['expires_at' => now()->subDays(10)]);

    $old = Activity::create(['log_name' => 'default', 'description' => 'old entry']);
    $old->created_at = now()->subDays(731);
    $old->save();

    $this->artisan('gdpr:enforce-retention')
        ->expectsOutput('Purged 1 notifications, 1 expired exports, 1 activity log entries.')
        ->assertExitCode(0);
});
