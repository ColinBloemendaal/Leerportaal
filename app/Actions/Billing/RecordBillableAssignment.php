<?php

declare(strict_types=1);

namespace App\Actions\Billing;

use App\Enums\AssignmentBillingState;
use App\Models\CourseAssignment;
use App\Models\InvoiceLine;
use App\Support\Money;
use Illuminate\Database\ConnectionInterface;

/**
 * CLAUDE.md §11: "Billable event = assigning one course to one cursist" --
 * this is that event, fired unconditionally at assignment time (see
 * App\Actions\Courses\AssignCourseToCursist). The 14-day free-revocation
 * rule is deliberately NOT a delay on billing; it's a later reversal
 * (waiver/credit) applied on top of an assignment that was already billed
 * here, same as most real invoicing: bill now, credit back if the grace
 * period condition is met.
 */
final readonly class RecordBillableAssignment
{
    public function __construct(
        private FindOrCreateDraftInvoice $findOrCreateDraftInvoice,
        private ConnectionInterface $db,
    ) {}

    public function __invoke(CourseAssignment $assignment): InvoiceLine
    {
        return $this->db->transaction(function () use ($assignment): InvoiceLine {
            $invoice = ($this->findOrCreateDraftInvoice)($assignment->reseller_id);

            $amountCents = $assignment->price_cents === null ? 0 : $assignment->price_cents->cents;

            $line = InvoiceLine::query()->create([
                'invoice_id' => $invoice->id,
                'course_assignment_id' => $assignment->id,
                'description' => $this->describe($assignment),
                'amount_cents' => $amountCents,
            ]);

            // Not Invoice::increment(): MoneyCast's get() returns a Money
            // object, not an int, so Eloquent's increment() would try to
            // evaluate "Money object + int" and throw a TypeError.
            $currentTotalCents = $invoice->total_cents === null ? 0 : $invoice->total_cents->cents;
            $invoice->total_cents = Money::fromCents($currentTotalCents + $amountCents);
            $invoice->save();

            $assignment->billing_state = AssignmentBillingState::Billed;
            $assignment->save();

            return $line;
        });
    }

    private function describe(CourseAssignment $assignment): string
    {
        $course = $assignment->course()->first();

        return $course === null ? "Course assignment #{$assignment->id}" : $course->title;
    }
}
