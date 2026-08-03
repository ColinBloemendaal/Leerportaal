<?php

declare(strict_types=1);

use App\Actions\Notifications\CheckPlatformHealthAndAlert;
use App\Enums\Role;
use App\Models\FailedJob;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Horizon\Contracts\WorkloadRepository;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function fakeEmptyWorkload(): void
{
    $fakeWorkload = new class implements WorkloadRepository
    {
        public function get()
        {
            return [];
        }
    };

    app()->instance(WorkloadRepository::class, $fakeWorkload);
}

it('alerts every super-admin once failed jobs exceed the threshold', function (): void {
    Notification::fake();
    fakeEmptyWorkload();

    FailedJob::factory()->count(11)->create(['failed_at' => now()->subHours(1)]);
    $superAdmin = User::factory()->platformStaff()->create(['platform_role' => Role::SuperAdmin]);
    User::factory()->platformStaff()->create(['platform_role' => Role::PlatformAdmin]);

    $alerted = app(CheckPlatformHealthAndAlert::class)();

    expect($alerted)->toBeTrue();
    Notification::assertSentTo($superAdmin, AdminAlertNotification::class);
    Notification::assertCount(1);
});

it('does not alert while failed jobs stay at or under the threshold', function (): void {
    Notification::fake();
    fakeEmptyWorkload();

    FailedJob::factory()->count(10)->create(['failed_at' => now()->subHours(1)]);
    User::factory()->platformStaff()->create(['platform_role' => Role::SuperAdmin]);

    $alerted = app(CheckPlatformHealthAndAlert::class)();

    expect($alerted)->toBeFalse();
    Notification::assertNothingSent();
});

it('does not alert again within the cooldown window', function (): void {
    Notification::fake();
    fakeEmptyWorkload();

    FailedJob::factory()->count(11)->create(['failed_at' => now()->subHours(1)]);
    User::factory()->platformStaff()->create(['platform_role' => Role::SuperAdmin]);

    $first = app(CheckPlatformHealthAndAlert::class)();
    $second = app(CheckPlatformHealthAndAlert::class)();

    expect($first)->toBeTrue()
        ->and($second)->toBeFalse();
    Notification::assertCount(1);
});
