<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\LazyCollection;

interface InvoiceRepository
{
    /**
     * The one invoice a billable event may still attach a line to for this
     * reseller -- null when there isn't one yet (the next task, "billable
     * event on course assignment," is what creates the first one).
     */
    public function currentDraftForReseller(int $resellerId): ?Invoice;

    /**
     * Same as currentDraftForReseller(), but with its lines and each
     * line's course assignment/cursist/klant eager-loaded -- only the
     * reseller billing dashboard's per-klant breakdown needs that, so it's
     * a separate method rather than always paying for the extra joins on
     * every other caller of the lean version above.
     */
    public function currentDraftForResellerWithLines(int $resellerId): ?Invoice;

    /**
     * The reseller's own past (non-draft) invoices, most recent period
     * first -- the billing dashboard's history section.
     *
     * @return Collection<int, Invoice>
     */
    public function historyForReseller(int $resellerId, int $limit = 12): Collection;

    /**
     * Every reseller's draft invoice whose period has ended and that has
     * at least one billed line -- an empty draft (nothing happened that
     * period) is never issued, not charged as EUR 0.00.
     *
     * @return LazyCollection<int, Invoice>
     */
    public function draftsReadyToIssue(): LazyCollection;

    /**
     * Every invoice a payment attempt has failed/expired/canceled for --
     * the dunning command's source of what to retry or, past the retry
     * limit, suspend the reseller over.
     *
     * @return LazyCollection<int, Invoice>
     */
    public function overdue(): LazyCollection;
}
