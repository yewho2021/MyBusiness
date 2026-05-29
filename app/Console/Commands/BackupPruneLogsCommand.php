<?php

namespace App\Console\Commands;

use App\Models\BackupLog;
use App\Models\BackupRun;
use Illuminate\Console\Command;

class BackupPruneLogsCommand extends Command
{
    protected $signature = 'backup:prune-logs
                            {--days=30 : Delete logs older than this many days}
                            {--keep-latest=5 : Always keep logs for the N most recent runs}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Prune old backup log entries to prevent table bloat';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $keepLatest = (int) $this->option('keep-latest');
        $dryRun = $this->option('dry-run');

        $this->info('');
        $this->info('Backup Log Pruning');
        $this->info('──────────────────────────────────');

        // Current state
        $totalLogs = BackupLog::count();
        $totalRuns = BackupRun::count();
        $this->info("Current state: {$totalLogs} log entries across {$totalRuns} backup runs");

        // Find run IDs to keep (latest N runs regardless of age)
        $keepRunIds = BackupRun::orderBy('created_at', 'desc')
            ->limit($keepLatest)
            ->pluck('id')
            ->toArray();

        $this->info("Keeping logs for latest {$keepLatest} runs: [" . implode(', ', $keepRunIds) . "]");

        // Find logs to delete: older than X days AND not in the keep list
        $cutoffDate = now()->subDays($days);
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')} ({$days} days ago)");

        $query = BackupLog::where('logged_at', '<', $cutoffDate);

        if (!empty($keepRunIds)) {
            $query->whereNotIn('run_id', $keepRunIds);
        }

        $deleteCount = $query->count();

        if ($deleteCount === 0) {
            $this->info('Nothing to prune — all logs are within retention policy.');
            return 0;
        }

        $this->info("Found {$deleteCount} log entries eligible for pruning.");

        if ($dryRun) {
            $this->warn("[DRY RUN] Would delete {$deleteCount} log entries.");

            // Show breakdown by run
            $breakdown = BackupLog::where('logged_at', '<', $cutoffDate)
                ->whereNotIn('run_id', $keepRunIds)
                ->selectRaw('run_id, COUNT(*) as cnt, MIN(logged_at) as oldest, MAX(logged_at) as newest')
                ->groupBy('run_id')
                ->orderBy('oldest')
                ->get();

            foreach ($breakdown as $row) {
                $this->line("  Run #{$row->run_id}: {$row->cnt} entries ({$row->oldest} → {$row->newest})");
            }

            return 0;
        }

        // Confirm if interactive
        if (!$this->option('no-interaction')) {
            if (!$this->confirm("Delete {$deleteCount} log entries?", true)) {
                $this->info('Aborted.');
                return 0;
            }
        }

        // Delete in chunks to avoid memory issues
        $deleted = 0;
        $chunkSize = 1000;

        while ($deleted < $deleteCount) {
            $batch = BackupLog::where('logged_at', '<', $cutoffDate)
                ->whereNotIn('run_id', $keepRunIds)
                ->limit($chunkSize)
                ->delete();

            if ($batch === 0) break;
            $deleted += $batch;
            $this->line("  Deleted {$deleted}/{$deleteCount}...");
        }

        $remaining = BackupLog::count();
        $this->info("Pruned {$deleted} log entries. Remaining: {$remaining}");

        return 0;
    }
}
