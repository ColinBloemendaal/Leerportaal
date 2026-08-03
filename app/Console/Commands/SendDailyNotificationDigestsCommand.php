<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Notifications\SendNotificationDigest;
use App\Contracts\Repositories\UserRepository;
use App\Enums\DigestFrequency;
use Illuminate\Console\Command;

final class SendDailyNotificationDigestsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'notifications:digest-daily';

    /**
     * @var string
     */
    protected $description = 'Email a digest to every user who has chosen the daily notification frequency';

    public function handle(UserRepository $users, SendNotificationDigest $sendDigest): int
    {
        $userCount = 0;
        $sentCount = 0;

        foreach ($users->withDigestFrequency(DigestFrequency::Daily) as $user) {
            if ($sendDigest($user)) {
                $sentCount++;
            }

            $userCount++;
        }

        $this->info("Evaluated {$userCount} user(s), sent {$sentCount} digest(s).");

        return self::SUCCESS;
    }
}
