<?php

use App\Console\Commands\SendAssignmentDeadlineRemindersCommand;
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

// Daily, not weekly: deadline/overdue reminders are dated relative to
// each assignment's own deadline_at, so this needs to run every day to
// catch whichever assignments are due for a reminder that day. The
// Action's own AssignmentReminder bookkeeping makes re-running (or a
// missed day catching up) safe -- nothing sends twice.
Schedule::command(SendAssignmentDeadlineRemindersCommand::class)->dailyAt('07:00');
