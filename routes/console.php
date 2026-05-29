<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduled Tasks ─────────────────────────────────
// cPanel cron entry (add once):
//   * * * * * cd /home/USER/PUBLIC_PATH && php artisan schedule:run >> /dev/null 2>&1

// Telegram scheduled reports — checks every minute for due reports
Schedule::command('telegram:cron')->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Prune old backup logs — daily at 3:00 AM
Schedule::command('backup:prune-logs --days=30 --keep-latest=5')->dailyAt('03:00')
    ->withoutOverlapping();

// Prune old activity log entries — weekly on Sunday at 4:00 AM
Schedule::command('activitylog:clean --days=90')->weeklyOn(0, '04:00')
    ->withoutOverlapping();
