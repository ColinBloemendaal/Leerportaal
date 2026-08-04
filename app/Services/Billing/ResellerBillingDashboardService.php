<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Contracts\Repositories\InvoiceRepository;
use App\DataTransferObjects\Billing\InvoiceHistoryEntryData;
use App\DataTransferObjects\Billing\KlantSpendData;
use App\DataTransferObjects\Billing\ResellerBillingDashboardData;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Support\Money;

final readonly class ResellerBillingDashboardService
{
    public function __construct(private InvoiceRepository $invoices) {}

    public function forReseller(int $resellerId): ResellerBillingDashboardData
    {
        $currentDraft = $this->invoices->currentDraftForResellerWithLines($resellerId);

        return new ResellerBillingDashboardData(
            currentPeriodStart: $currentDraft === null ? null : $currentDraft->period_start->toDateString(),
            currentPeriodEnd: $currentDraft === null ? null : $currentDraft->period_end->toDateString(),
            currentPeriodSubtotal: $currentDraft === null ? Money::fromCents(0) : $this->moneyOrZero($currentDraft->subtotal_cents),
            klantBreakdown: $currentDraft === null ? [] : $this->klantBreakdown($currentDraft),
            history: array_values($this->invoices->historyForReseller($resellerId)
                ->map($this->historyEntry(...))
                ->all()),
        );
    }

    /**
     * @return list<KlantSpendData>
     */
    private function klantBreakdown(Invoice $invoice): array
    {
        /** @var array<int|string, array{klantId: ?int, klantName: string, cents: int}> $totals */
        $totals = [];

        foreach ($invoice->lines as $line) {
            [$klantId, $klantName] = $this->klantFor($line);
            $key = $klantId ?? 'other';
            $cents = $line->amount_cents === null ? 0 : $line->amount_cents->cents;

            $totals[$key] ??= ['klantId' => $klantId, 'klantName' => $klantName, 'cents' => 0];
            $totals[$key]['cents'] += $cents;
        }

        return array_values(array_map(
            fn (array $entry): KlantSpendData => new KlantSpendData(
                klantId: $entry['klantId'],
                klantName: $entry['klantName'],
                subtotal: Money::fromCents($entry['cents']),
            ),
            $totals,
        ));
    }

    /**
     * @return array{0: ?int, 1: string}
     */
    private function klantFor(InvoiceLine $line): array
    {
        $klant = $line->courseAssignment?->user?->resellerKlant;

        return $klant === null ? [null, trans('Other')] : [$klant->id, $klant->name];
    }

    private function historyEntry(Invoice $invoice): InvoiceHistoryEntryData
    {
        return new InvoiceHistoryEntryData(
            id: $invoice->id,
            periodStart: $invoice->period_start->toDateString(),
            periodEnd: $invoice->period_end->toDateString(),
            status: $invoice->status->value,
            total: $this->moneyOrZero($invoice->total_cents),
            issuedAt: $invoice->issued_at?->toIso8601String(),
            paidAt: $invoice->paid_at?->toIso8601String(),
        );
    }

    private function moneyOrZero(?Money $money): Money
    {
        return $money === null ? Money::fromCents(0) : $money;
    }
}
