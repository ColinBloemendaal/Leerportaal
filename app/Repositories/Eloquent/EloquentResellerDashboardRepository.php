<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ResellerDashboardRepository;
use App\Enums\AssignmentBillingState;
use App\Models\CourseAssignment;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Tenancy\TenantContext;

final class EloquentResellerDashboardRepository implements ResellerDashboardRepository
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function klantCount(): int
    {
        // ResellerKlant is TenantScoped -- the global scope already
        // restricts this to the current reseller.
        return ResellerKlant::query()->count();
    }

    public function cursistCount(): int
    {
        // User has no TenantScope (mixed platform/reseller ownership),
        // so the reseller_id filter is explicit here, same reasoning as
        // EloquentCourseRepository.
        return User::query()
            ->where('reseller_id', $this->tenantContext->id())
            ->whereNotNull('resellerklant_id')
            ->count();
    }

    public function assignmentCount(): int
    {
        return CourseAssignment::query()->count();
    }

    public function billedSpendCents(): int
    {
        return $this->spendCentsFor(AssignmentBillingState::Billed);
    }

    public function pendingSpendCents(): int
    {
        return $this->spendCentsFor(AssignmentBillingState::Pending);
    }

    private function spendCentsFor(AssignmentBillingState $state): int
    {
        return (int) CourseAssignment::query()->where('billing_state', $state)->sum('price_cents');
    }
}
