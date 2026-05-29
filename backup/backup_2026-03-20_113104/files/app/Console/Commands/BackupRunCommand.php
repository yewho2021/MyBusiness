<?php

namespace App\Console\Commands;

use App\Models\BackupJob;
use App\Models\BackupRun;
use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupRunCommand extends Command
{
    protected $signature = 'backup:run {job? : The backup job ID (optional, runs all active if omitted)}';
    protected $description = 'Run a backup job';

    public function handle(BackupService $service): int
    {
        $jobId = $this->argument('job');

        if ($jobId) {
            $job = BackupJob::find($jobId);
            if (!$job) {
                $this->error("Backup job #{$jobId} not found.");
                return 1;
            }
            $this->runJob($job, $service);
        } else {
            $jobs = BackupJob::where('is_active', true)->get();
            if ($jobs->isEmpty()) {
                $this->info('No active backup jobs found.');
                return 0;
            }
            foreach ($jobs as $job) {
                $this->runJob($job, $service);
            }
        }

        return 0;
    }

    protected function runJob(BackupJob $job, BackupService $service): void
    {
        $this->info("Running backup job: {$job->name} (#{$job->id})");

        $run = BackupRun::create([
            'job_id' => $job->id,
            'status' => BackupRun::STATUS_PENDING,
            'include_paths' => $job->include_paths,
            'exclude_paths' => $job->exclude_paths,
            'include_database' => $job->include_database,
        ]);

        try {
            $service->execute($run);
            $this->info("Backup completed: {$run->folder_name} ({$run->formatted_size})");
        } catch (\Exception $e) {
            $this->error("Backup failed: " . $e->getMessage());
        }
    }
}
