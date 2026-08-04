<?php

declare(strict_types=1);

use App\Models\Reseller;
use App\Services\Gdpr\RetentionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    config([
        'gdpr.retention.activity_log_days' => 730,
        'gdpr.retention.notifications_days' => 180,
        'gdpr.retention.expired_exports_grace_days' => 7,
    ]);

    $this->policy = new RetentionPolicy;
});

it('uses the platform default when a reseller has no override', function (): void {
    $reseller = Reseller::factory()->create(['settings' => null]);

    expect($this->policy->notificationDaysFor($reseller))->toBe(180)
        ->and($this->policy->expiredExportsGraceDaysFor($reseller))->toBe(7)
        ->and($this->policy->notificationDaysFor(null))->toBe(180);
});

it('lets a reseller shorten its own retention window', function (): void {
    $reseller = Reseller::factory()->create(['settings' => ['retention' => ['notifications_days' => 30]]]);

    expect($this->policy->notificationDaysFor($reseller))->toBe(30);
});

it('ignores a reseller override that would lengthen retention past the platform default', function (): void {
    $reseller = Reseller::factory()->create(['settings' => ['retention' => ['notifications_days' => 3650]]]);

    expect($this->policy->notificationDaysFor($reseller))->toBe(180);
});

it('ignores an invalid (non-positive or non-integer) override', function (): void {
    $reseller = Reseller::factory()->create(['settings' => ['retention' => ['notifications_days' => 0]]]);

    expect($this->policy->notificationDaysFor($reseller))->toBe(180);
});

it('never lets a reseller configure the activity log retention window', function (): void {
    $reseller = Reseller::factory()->create(['settings' => ['retention' => ['activity_log_days' => 1]]]);

    expect($this->policy->activityLogDays())->toBe(730);
});
