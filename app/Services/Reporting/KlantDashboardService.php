<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\DataTransferObjects\Dashboard\CursistAssignmentRowData;
use App\DataTransferObjects\Reporting\KlantCursistSummaryData;
use App\DataTransferObjects\Reporting\KlantDashboardData;
use App\Enums\AssignmentStatus;
use App\Models\ResellerKlant;
use App\Models\User;
use App\Services\Progress\CursistDashboardService;
use Illuminate\Support\Collection;

/**
 * Reuses CursistDashboardService's per-assignment status/progress
 * calculation (already built in Phase 5) for every cursist in the
 * klant, rather than duplicating it -- this only aggregates those rows
 * into a per-cursist summary.
 */
final readonly class KlantDashboardService
{
    public function __construct(private CursistDashboardService $cursistDashboard) {}

    public function forKlant(ResellerKlant $klant): KlantDashboardData
    {
        $cursisten = array_values($klant->cursisten()->get()
            ->map(fn (User $cursist): KlantCursistSummaryData => $this->summaryFor($cursist))
            ->all());

        return new KlantDashboardData(
            cursistCount: count($cursisten),
            cursisten: $cursisten,
        );
    }

    private function summaryFor(User $cursist): KlantCursistSummaryData
    {
        /** @var Collection<int, CursistAssignmentRowData> $rows */
        $rows = $this->cursistDashboard->forUser($cursist);

        return new KlantCursistSummaryData(
            userId: $cursist->id,
            name: $cursist->name,
            assignedCount: $rows->count(),
            inProgressCount: $rows->filter(fn (CursistAssignmentRowData $row): bool => $row->status === AssignmentStatus::InProgress)->count(),
            completedCount: $rows->filter(fn (CursistAssignmentRowData $row): bool => $row->status === AssignmentStatus::Completed)->count(),
        );
    }
}
