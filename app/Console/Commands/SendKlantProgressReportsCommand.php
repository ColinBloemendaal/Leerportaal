<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Reporting\SendKlantProgressReport;
use App\Contracts\Repositories\ResellerKlantRepository;
use Illuminate\Console\Command;

final class SendKlantProgressReportsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reports:klant-progress';

    /**
     * @var string
     */
    protected $description = "Email every klant's admins a weekly cursist progress report";

    public function handle(SendKlantProgressReport $send, ResellerKlantRepository $klanten): int
    {
        $klantCount = 0;
        $recipientCount = 0;

        foreach ($klanten->all() as $klant) {
            $recipientCount += $send($klant);
            $klantCount++;
        }

        $this->info("Sent progress reports for {$klantCount} klant(en) to {$recipientCount} recipient(s).");

        return self::SUCCESS;
    }
}
