<?php

use App\Console\Commands\SendKlantProgressReportsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// First real use of the scheduler in this codebase -- the Ploi cron
// entry pointing at `php artisan schedule:run` (set up in Phase 1) has
// had nothing to actually run until now.
Schedule::command(SendKlantProgressReportsCommand::class)->weeklyOn(1, '06:00');
