<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\InvoiceRepository;
use App\DataTransferObjects\Filtering\FilterableSpec;
use App\DataTransferObjects\Filtering\FilterRequestData;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Support\Filtering\QueryFilterApplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\LazyCollection;

final class EloquentInvoiceRepository implements InvoiceRepository
{
    public function __construct(private readonly QueryFilterApplier $filters) {}

    public function currentDraftForReseller(int $resellerId): ?Invoice
    {
        return Invoice::query()
            ->where('reseller_id', $resellerId)
            ->where('status', InvoiceStatus::Draft)
            ->latest('period_start')
            ->first();
    }

    public function currentDraftForResellerWithLines(int $resellerId): ?Invoice
    {
        return Invoice::query()
            ->where('reseller_id', $resellerId)
            ->where('status', InvoiceStatus::Draft)
            ->with('lines.courseAssignment.user.resellerKlant')
            ->latest('period_start')
            ->first();
    }

    public function historyForReseller(int $resellerId, int $limit = 12): Collection
    {
        return Invoice::query()
            ->where('reseller_id', $resellerId)
            ->where('status', '!=', InvoiceStatus::Draft)
            ->latest('period_start')
            ->limit($limit)
            ->get();
    }

    public function draftsReadyToIssue(): LazyCollection
    {
        // withoutTenantScope(): a batch invoice-issuing run spans every
        // reseller by definition, same reasoning as
        // CourseAssignmentRepository::dueForDeadlineEvaluation().
        return Invoice::query()
            ->withoutTenantScope()
            ->where('status', InvoiceStatus::Draft)
            ->where('period_end', '<', now())
            ->where('subtotal_cents', '>', 0)
            ->cursor();
    }

    public function overdue(): LazyCollection
    {
        // withoutTenantScope(): same reasoning as draftsReadyToIssue() --
        // dunning runs across every reseller in one batch.
        return Invoice::query()
            ->withoutTenantScope()
            ->where('status', InvoiceStatus::Overdue)
            ->cursor();
    }

    public function paginate(FilterRequestData $filters, int $perPage = 15): LengthAwarePaginator
    {
        $spec = new FilterableSpec(
            allowedSorts: ['period_start', 'status', 'total_cents'],
            allowedFilters: ['status'],
            defaultSort: 'period_start',
        );

        return $this->filters->apply(Invoice::query(), $filters, $spec)->paginate($perPage);
    }
}
