<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\AssignmentBillingState;
use App\Enums\InvoiceStatus;
use App\Models\CourseAssignment;
use App\Models\InvoiceLine;
use App\Support\Money;
use App\Tenancy\TenantContext;

/**
 * The financial-reversal half of a free (within 14 days, never opened)
 * revocation -- see App\Actions\Courses\RevokeCourseAssignment, which
 * decides *whether* a revocation qualifies as free before calling this.
 *
 * A line still sitting on a Draft invoice is reversed directly (removed,
 * invoice subtotal reduced) -- nothing has been issued yet, so there's
 * nothing to preserve. Once the invoice is Issued/Paid, CLAUDE.md §11 says
 * it's immutable: the line and totals stay exactly as issued, and the
 * correction is a separate IssueCreditNote record instead, for the
 * already-invoiced amount.
 */
final readonly class WaiveAssignmentBilling
{
    public function __construct(
        private IssueCreditNote $issueCreditNote,
        private TenantContext $tenantContext,
    ) {}

    public function __invoke(CourseAssignment $assignment): void
    {
        if ($assignment->billing_state !== AssignmentBillingState::Billed) {
            return;
        }

        $line = InvoiceLine::query()->where('course_assignment_id', $assignment->id)->first();

        if ($line !== null) {
            $invoice = $line->invoice()->first();

            if ($invoice !== null) {
                $reseller = $invoice->reseller()->first();

                if ($reseller !== null) {
                    $this->tenantContext->set($reseller);
                }

                $lineAmountCents = $line->amount_cents === null ? 0 : $line->amount_cents->cents;

                if ($invoice->status === InvoiceStatus::Draft) {
                    $currentSubtotalCents = $invoice->subtotal_cents === null ? 0 : $invoice->subtotal_cents->cents;
                    $invoice->subtotal_cents = Money::fromCents(max(0, $currentSubtotalCents - $lineAmountCents));
                    $invoice->save();

                    $line->delete();
                } else {
                    ($this->issueCreditNote)($invoice, $lineAmountCents, '14-day free revocation');
                }
            }
        }

        $assignment->billing_state = AssignmentBillingState::Waived;
        $assignment->save();
    }
}
