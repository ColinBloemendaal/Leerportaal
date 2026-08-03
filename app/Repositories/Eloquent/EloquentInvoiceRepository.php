<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\InvoiceRepository;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\LazyCollection;

final class EloquentInvoiceRepository implements InvoiceRepository
{
    public function currentDraftForReseller(int $resellerId): ?Invoice
    {
        return Invoice::query()
            ->where('reseller_id', $resellerId)
            ->where('status', InvoiceStatus::Draft)
            ->latest('period_start')
            ->first();
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
}
