<?php

use App\Console\Commands\CheckPlatformHealthCommand;
use App\Console\Commands\SendAssignmentDeadlineRemindersCommand;
use App\Console\Commands\SendDailyNotificationDigestsCommand;
use App\Console\Commands\SendKlantProgressReportsCommand;
use App\Console\Commands\SendWeeklyNotificationDigestsCommand;
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

// Hourly: the Action's own cache-backed cooldown (24h) is what actually
// prevents spamming super-admins while a failed-job spike persists, not
// the schedule interval -- this just needs to run often enough to catch
// a spike promptly.
Schedule::command(CheckPlatformHealthCommand::class)->hourly();

// notification_digest_sent_at (not a fixed "last 24h"/"last 7 days"
// window) is what actually makes these safe to run at these times --
// each command only ever picks up what accumulated since that user's
// own last send, so a late or re-run doesn't duplicate or skip.
Schedule::command(SendDailyNotificationDigestsCommand::class)->dailyAt('08:00');
Schedule::command(SendWeeklyNotificationDigestsCommand::class)->weeklyOn(1, '08:00');
