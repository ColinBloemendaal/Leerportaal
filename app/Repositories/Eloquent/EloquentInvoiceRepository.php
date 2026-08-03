<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\InvoiceRepository;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;

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
}
