<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Notifications\CheckPlatformHealthAndAlert;
use Illuminate\Console\Command;

final class CheckPlatformHealthCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'health:check';

    /**
     * @var string
     */
    protected $description = 'Alert platform super-admins when failed jobs spike above the threshold';

    public function handle(CheckPlatformHealthAndAlert $checkAndAlert): int
    {
        $alerted = $checkAndAlert();

        $this->info($alerted ? 'Alert sent.' : 'No alert needed.');

        return self::SUCCESS;
    }
}
