<?php

declare(strict_types=1);

use App\Actions\Gdpr\PurgeOldActivityLogEntries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('deletes only activity log entries older than the platform-wide retention window', function (): void {
    config(['gdpr.retention.activity_log_days' => 730]);

    $old = Activity::create(['log_name' => 'default', 'description' => 'old entry']);
    $old->created_at = now()->subDays(731);
    $old->save();

    $recent = Activity::create(['log_name' => 'default', 'description' => 'recent entry']);
    $recent->created_at = now()->subDays(1);
    $recent->save();

    $deleted = app(PurgeOldActivityLogEntries::class)();

    expect($deleted)->toBe(1)
        ->and(Activity::query()->find($recent->id))->not->toBeNull()
        ->and(Activity::query()->find($old->id))->toBeNull();
});
