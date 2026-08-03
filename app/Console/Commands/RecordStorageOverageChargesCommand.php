<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Billing\RecordStorageOverageCharge;
use App\Contracts\Repositories\ResellerRepository;
use Illuminate\Console\Command;

final class RecordStorageOverageChargesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'billing:storage-overage';

    /**
     * @var string
     */
    protected $description = 'Charge (or credit back) every reseller currently over their included 5 GB of storage';

    public function handle(ResellerRepository $resellers, RecordStorageOverageCharge $recordCharge): int
    {
        $count = 0;

        foreach ($resellers->all() as $reseller) {
            if ($recordCharge($reseller) !== null) {
                $count++;
            }
        }

        $this->info("Recorded storage overage charges for {$count} reseller(s).");

        return self::SUCCESS;
    }
}
