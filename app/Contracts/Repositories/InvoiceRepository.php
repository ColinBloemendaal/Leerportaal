<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Invoice;

interface InvoiceRepository
{
    /**
     * The one invoice a billable event may still attach a line to for this
     * reseller -- null when there isn't one yet (the next task, "billable
     * event on course assignment," is what creates the first one).
     */
    public function currentDraftForReseller(int $resellerId): ?Invoice;
}
