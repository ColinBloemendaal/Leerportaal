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
 * Only reverses a line still sitting on a Draft invoice: CLAUDE.md §11
 * says invoices are immutable once issued, so a waiver against an already
 * Issued/Paid invoice can't touch that invoice's lines or total here --
 * that correction needs a credit note, a separate, not-yet-built sub-task.
 * Documented gap, not silently papered over: `billing_state` still flips
 * to Waived either way (the assignment itself is no longer billable going
 * forward), but the money already invoiced stays invoiced until a credit
 * note exists.
 */
final readonly class WaiveAssignmentBilling
{
    public function __construct(private TenantContext $tenantContext) {}

    public function __invoke(CourseAssignment $assignment): void
    {
        if ($assignment->billing_state !== AssignmentBillingState::Billed) {
            return;
        }

        $line = InvoiceLine::query()->where('course_assignment_id', $assignment->id)->first();

        if ($line !== null) {
            $invoice = $line->invoice()->first();

            if ($invoice !== null && $invoice->status === InvoiceStatus::Draft) {
                $reseller = $invoice->reseller()->first();

                if ($reseller !== null) {
                    $this->tenantContext->set($reseller);
                }

                $currentTotalCents = $invoice->total_cents === null ? 0 : $invoice->total_cents->cents;
                $lineAmountCents = $line->amount_cents === null ? 0 : $line->amount_cents->cents;
                $invoice->total_cents = Money::fromCents(max(0, $currentTotalCents - $lineAmountCents));
                $invoice->save();

                $line->delete();
            }
        }

        $assignment->billing_state = AssignmentBillingState::Waived;
        $assignment->save();
    }
}
