<?php

namespace App\Jobs;

use App\Models\BackupRun;
use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RestoreBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    public function __construct(
        public BackupRun $backupRun
    ) {}

    public function handle(BackupService $service): void
    {
        $service->restore($this->backupRun);
    }

    public function failed(\Throwable $exception): void
    {
        $this->backupRun->update([
            'status' => BackupRun::STATUS_FAILED,
            'error_message' => 'Restore failed: ' . $exception->getMessage(),
        ]);
    }
}
