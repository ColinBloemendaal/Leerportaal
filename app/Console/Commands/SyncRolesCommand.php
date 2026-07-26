<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Permissions\SeedRolesForReseller;
use App\Contracts\Repositories\ResellerRepository;
use Illuminate\Console\Command;

/**
 * Backfill for resellers that existed before role seeding was wired to
 * Reseller's `created` event -- safe to re-run any time since
 * SeedRolesForReseller is idempotent.
 */
final class SyncRolesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'permissions:sync-roles';

    /**
     * @var string
     */
    protected $description = 'Ensure every reseller has the full set of team-scoped roles';

    public function handle(SeedRolesForReseller $seedRoles, ResellerRepository $resellers): int
    {
        $count = 0;

        foreach ($resellers->all() as $reseller) {
            $seedRoles($reseller);
            $count++;
        }

        $this->info("Synced roles for {$count} reseller(s).");

        return self::SUCCESS;
    }
}
