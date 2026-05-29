<?php

namespace App\Console\Commands;

use App\Models\BackupRun;
use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore {backup_id : The backup run ID to restore}';
    protected $description = 'Restore from a backup snapshot';

    public function handle(BackupService $service): int
    {
        $run = BackupRun::find($this->argument('backup_id'));

        if (!$run) {
            $this->error('Backup not found.');
            return 1;
        }

        if ($run->status !== BackupRun::STATUS_COMPLETED) {
            $this->error('Can only restore from completed backups.');
            return 1;
        }

        if (!$this->confirm("Restore from backup '{$run->folder_name}'? This will overwrite existing files.")) {
            $this->info('Restore cancelled.');
            return 0;
        }

        $this->info('Starting restore...');

        try {
            $service->restore($run);
            $this->info('Restore completed successfully.');
        } catch (\Exception $e) {
            $this->error('Restore failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
