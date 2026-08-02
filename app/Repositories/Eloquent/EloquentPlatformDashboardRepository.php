<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PlatformDashboardRepository;
use App\Enums\AssignmentBillingState;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\User;

final class EloquentPlatformDashboardRepository implements PlatformDashboardRepository
{
    public function resellerCount(): int
    {
        return Reseller::query()->count();
    }

    public function userCount(): int
    {
        return User::query()->count();
    }

    public function courseCount(): int
    {
        return Course::query()->count();
    }

    public function billedRevenueCents(): int
    {
        return $this->revenueCentsFor(AssignmentBillingState::Billed);
    }

    public function pendingRevenueCents(): int
    {
        return $this->revenueCentsFor(AssignmentBillingState::Pending);
    }

    private function revenueCentsFor(AssignmentBillingState $state): int
    {
        return (int) CourseAssignment::query()
            // withoutTenantScope(): a platform-wide total spans every
            // reseller by definition -- this repository only ever runs
            // in the platform admin dashboard context.
            ->withoutTenantScope()
            ->where('billing_state', $state)
            ->sum('price_cents');
    }
}
