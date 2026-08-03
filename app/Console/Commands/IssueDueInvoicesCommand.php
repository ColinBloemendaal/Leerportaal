<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Billing\IssueInvoice;
use App\Contracts\Repositories\InvoiceRepository;
use Illuminate\Console\Command;

final class IssueDueInvoicesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'billing:issue-invoices';

    /**
     * @var string
     */
    protected $description = 'Issue every reseller draft invoice whose period has ended and that has at least one billed line';

    public function handle(InvoiceRepository $invoices, IssueInvoice $issue): int
    {
        $count = 0;

        foreach ($invoices->draftsReadyToIssue() as $invoice) {
            $issue($invoice);
            $count++;
        }

        $this->info("Issued {$count} invoice(s).");

        return self::SUCCESS;
    }
}
