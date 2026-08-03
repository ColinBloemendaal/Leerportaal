<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Billing\ProcessOverdueInvoice;
use App\Contracts\Repositories\InvoiceRepository;
use Illuminate\Console\Command;

final class ProcessOverdueInvoicesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'billing:process-overdue';

    /**
     * @var string
     */
    protected $description = 'Retry payment for every overdue invoice, or suspend the reseller once retries are exhausted';

    public function handle(InvoiceRepository $invoices, ProcessOverdueInvoice $process): int
    {
        $count = 0;

        foreach ($invoices->overdue() as $invoice) {
            $process($invoice);
            $count++;
        }

        $this->info("Processed {$count} overdue invoice(s).");

        return self::SUCCESS;
    }
}
