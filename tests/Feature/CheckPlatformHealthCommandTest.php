<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\FailedJob;
use App\Models\User;
use App\Notifications\AdminAlertNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Horizon\Contracts\WorkloadRepository;

it('alerts super-admins when the command runs against an unhealthy platform', function (): void {
    Notification::fake();

    $fakeWorkload = new class implements WorkloadRepository
    {
        public function get()
        {
            return [];
        }
    };
    app()->instance(WorkloadRepository::class, $fakeWorkload);

    FailedJob::factory()->count(11)->create(['failed_at' => now()->subHours(1)]);
    $superAdmin = User::factory()->platformStaff()->create(['platform_role' => Role::SuperAdmin]);

    $this->artisan('health:check')->assertSuccessful();

    Notification::assertSentTo($superAdmin, AdminAlertNotification::class);
});
