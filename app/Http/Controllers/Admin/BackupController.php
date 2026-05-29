<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBackupJobRequest;
use App\Models\BackupJob;
use App\Models\BackupRun;
use App\Models\BackupLog;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    // ─── DASHBOARD ─────────────────────────────────────────────
    public function index()
    {
        $jobs = BackupJob::withCount('runs')->orderBy('created_at', 'desc')->get();
        $recentRuns = BackupRun::with('job')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $stats = [
            'total_jobs' => BackupJob::count(),
            'active_jobs' => BackupJob::where('is_active', true)->count(),
            'total_backups' => BackupRun::where('status', 'completed')->count(),
            'total_size' => BackupRun::where('status', 'completed')->sum('total_size'),
        ];

        return view('admin.pages.backup.index', compact('jobs', 'recentRuns', 'stats'));
    }

    // ─── JOB MANAGEMENT ────────────────────────────────────────
    public function jobs()
    {
        $jobs = BackupJob::with('latestRun')->orderBy('created_at', 'desc')->get();
        return view('admin.pages.backup.jobs', compact('jobs'));
    }

    public function storeJob(StoreBackupJobRequest $request)
    {
        $adminId = $request->attributes->get('admin_id');

        BackupJob::create([
            'name' => $request->name,
            'frequency' => $request->frequency,
            'cron_expression' => $request->frequency === 'custom' ? $request->cron_expression : null,
            'destination_path' => $request->destination_path ?: null,
            'include_paths' => $this->parsePathList($request->include_paths),
            'exclude_paths' => $this->parsePathList($request->exclude_paths),
            'exclude_extensions' => $this->parsePathList($request->exclude_extensions),
            'include_database' => $request->boolean('include_database', true),
            'retention_count' => $request->retention_count ?? 10,
            'is_active' => true,
            'created_by' => $adminId,
        ]);

        return redirect()->route('admin.backup.jobs')->with('success', 'Backup job created.');
    }

    public function updateJob(StoreBackupJobRequest $request, $id)
    {
        $job = BackupJob::findOrFail($id);

        $job->update([
            'name' => $request->name,
            'frequency' => $request->frequency,
            'cron_expression' => $request->frequency === 'custom' ? $request->cron_expression : null,
            'destination_path' => $request->destination_path ?: null,
            'include_paths' => $this->parsePathList($request->include_paths),
            'exclude_paths' => $this->parsePathList($request->exclude_paths),
            'exclude_extensions' => $this->parsePathList($request->exclude_extensions),
            'include_database' => $request->boolean('include_database', true),
            'retention_count' => $request->retention_count ?? 10,
        ]);

        return redirect()->route('admin.backup.jobs')->with('success', 'Backup job updated.');
    }

    public function deleteJob($id)
    {
        $job = BackupJob::findOrFail($id);
        $job->runs()->each(function ($run) {
            $run->logs()->delete();
        });
        $job->runs()->delete();
        $job->delete();

        return redirect()->route('admin.backup.jobs')->with('success', 'Backup job deleted.');
    }

    public function toggleJob($id)
    {
        $job = BackupJob::findOrFail($id);
        $job->update(['is_active' => !$job->is_active]);

        return redirect()->route('admin.backup.jobs')->with('success', 'Job ' . ($job->is_active ? 'enabled' : 'disabled') . '.');
    }

    // ─── RUN BACKUP ────────────────────────────────────────────
    public function runNow($jobId)
    {
        $job = BackupJob::findOrFail($jobId);

        $run = BackupRun::create([
            'job_id' => $job->id,
            'status' => BackupRun::STATUS_PENDING,
            'destination_path' => $job->destination_path,
            'include_paths' => $job->include_paths,
            'exclude_paths' => $job->exclude_paths,
            'exclude_extensions' => $job->exclude_extensions,
            'include_database' => $job->include_database,
        ]);

        // Redirect immediately to logs page — backup will be triggered via AJAX
        return redirect()->route('admin.backup.logs', $run->id);
    }

    public function runManual(Request $request)
    {
        $request->validate([
            'include_paths' => 'nullable|string',
            'exclude_paths' => 'nullable|string',
            'include_database' => 'nullable|boolean',
        ]);

        $run = BackupRun::create([
            'job_id' => null,
            'status' => BackupRun::STATUS_PENDING,
            'include_paths' => $this->parsePathList($request->include_paths),
            'exclude_paths' => $this->parsePathList($request->exclude_paths),
            'include_database' => $request->boolean('include_database', true),
        ]);

        return redirect()->route('admin.backup.logs', $run->id);
    }

    // ─── EXECUTE BACKUP VIA AJAX ──────────────────────────────
    public function executeRun($runId)
    {
        $run = BackupRun::findOrFail($runId);

        if ($run->status !== BackupRun::STATUS_PENDING) {
            return response()->json(['status' => 'already_started']);
        }

        // Release session lock so polling requests aren't blocked
        session()->save();
        if (function_exists('session_write_close')) {
            session_write_close();
        }

        set_time_limit(600);
        try {
            app(BackupService::class)->execute($run);

            // Auto-prune old backup logs
            $this->autoPruneLogs();

            return response()->json(['status' => 'completed']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    // ─── EXECUTE RESTORE VIA AJAX ─────────────────────────────
    public function restoreExecuteAjax($runId)
    {
        $run = BackupRun::findOrFail($runId);

        if ($run->status !== BackupRun::STATUS_COMPLETED) {
            return response()->json(['status' => 'error', 'error' => 'Can only restore completed backups']);
        }

        $run->update(['processed_files' => 0, 'error_message' => null]);

        // Release session lock so polling requests aren't blocked
        session()->save();
        if (function_exists('session_write_close')) {
            session_write_close();
        }

        set_time_limit(600);
        try {
            app(BackupService::class)->restore($run);
            return response()->json(['status' => 'completed']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }

    // ─── HISTORY + LOGS ────────────────────────────────────────
    public function history()
    {
        $runs = BackupRun::with('job')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.pages.backup.history', compact('runs'));
    }

    public function logs($runId)
    {
        $run = BackupRun::with('job')->findOrFail($runId);

        $query = BackupLog::where('run_id', $runId)->orderBy('id', 'asc');
        $perPage = 200;

        // For completed backups with no page param, go to last page
        if (!request()->has('page') && in_array($run->status, ['completed', 'failed', 'restored'])) {
            $total = $query->count();
            $lastPage = max(1, (int) ceil($total / $perPage));
            if ($lastPage > 1) {
                return redirect()->route('admin.backup.logs', ['id' => $runId, 'page' => $lastPage]);
            }
        }

        $logs = $query->paginate($perPage);

        return view('admin.pages.backup.logs', compact('run', 'logs'));
    }

    // ─── PROGRESS API (AJAX) ──────────────────────────────────
    public function progress($runId)
    {
        $run = BackupRun::findOrFail($runId);

        // Get logs after a given ID (for incremental updates)
        $afterId = request('after_id', 0);
        $recentLogs = BackupLog::where('run_id', $runId)
            ->where('id', '>', $afterId)
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get();

        return response()->json([
            'status' => $run->status,
            'progress' => $run->progress,
            'processed_files' => $run->processed_files,
            'total_files' => $run->total_files,
            'total_size' => $run->formatted_size,
            'error_message' => $run->error_message,
            'started_at' => $run->started_at ? $run->started_at->format('d M Y H:i:s') : null,
            'started_ts' => $run->started_at ? $run->started_at->timestamp : null,
            'folder_name' => $run->folder_name,
            'logs' => $recentLogs->map(fn($log) => [
                'id' => $log->id,
                'level' => $log->level,
                'message' => $log->message,
                'time' => $log->logged_at->format('H:i:s'),
            ]),
        ]);
    }

    // ─── RESTORE ───────────────────────────────────────────────
    public function restoreConfirm($runId)
    {
        $run = BackupRun::with('job')->findOrFail($runId);

        if ($run->status !== BackupRun::STATUS_COMPLETED) {
            return redirect()->route('admin.backup.history')->with('error', 'Can only restore completed backups.');
        }

        // Read metadata — from folder or ZIP
        $metadata = null;
        $metadataFile = $run->getBackupPath() . '/metadata.json';

        if (file_exists($metadataFile)) {
            $metadata = json_decode(file_get_contents($metadataFile), true);
        } elseif ($run->hasZip()) {
            try {
                $zip = new \ZipArchive();
                if ($zip->open($run->getZipPath()) === true) {
                    $content = $zip->getFromName('metadata.json');
                    if ($content !== false) $metadata = json_decode($content, true);
                    $zip->close();
                }
            } catch (\Exception $e) {}
        }

        return view('admin.pages.backup.restore', compact('run', 'metadata'));
    }

    public function restoreExecute($runId)
    {
        $run = BackupRun::findOrFail($runId);

        if ($run->status !== BackupRun::STATUS_COMPLETED) {
            return redirect()->route('admin.backup.history')->with('error', 'Can only restore completed backups.');
        }

        // Mark as pending restore — AJAX will trigger actual restore
        $run->update([
            'processed_files' => 0,
            'error_message' => null,
            'status' => BackupRun::STATUS_RESTORING,
        ]);

        return redirect()->route('admin.backup.logs', $run->id);
    }

    // ─── DOWNLOAD BACKUP ZIP ──────────────────────────────────
    public function download($runId)
    {
        $run = BackupRun::findOrFail($runId);

        if (!$run->hasZip()) {
            return redirect()->back()->with('error', 'No ZIP file found for this backup. Only new backups are packaged as ZIP.');
        }

        $zipPath = $run->getZipPath();
        $downloadName = $run->folder_name . '.zip';

        return response()->download($zipPath, $downloadName);
    }

    // ─── DELETE BACKUP ─────────────────────────────────────────
    public function deleteBackup($runId)
    {
        $run = BackupRun::findOrFail($runId);
        app(BackupService::class)->deleteBackup($run);

        return redirect()->back()->with('success', 'Backup deleted.');
    }

    public function deleteLogs($runId)
    {
        $run = BackupRun::findOrFail($runId);
        $count = $run->logs()->count();
        $run->logs()->delete();
        return redirect()->route('admin.backup.logs', $runId)->with('success', "Deleted {$count} log entries.");
    }

    // ─── HELPERS ───────────────────────────────────────────────
    protected function parsePathList(?string $input): array
    {
        if (!$input)
            return [];
        return array_values(array_filter(
            array_map('trim', preg_split('/[\n,]+/', $input))
        ));
    }

    /**
     * Auto-prune old backup logs after each backup.
     */
    protected function autoPruneLogs(): void
    {
        try {
            $days = (int) \App\Models\Configuration::get('backup_log_retention_days', 30);
            if ($days <= 0) return;

            $keepLatest = 5;
            $cutoff = now()->subDays($days);

            $keepRunIds = BackupRun::orderBy('created_at', 'desc')
                ->limit($keepLatest)
                ->pluck('id')
                ->toArray();

            $deleted = BackupLog::where('logged_at', '<', $cutoff)
                ->whereNotIn('run_id', $keepRunIds)
                ->delete();

            if ($deleted > 0) {
                \Illuminate\Support\Facades\Log::info("Backup auto-prune: deleted {$deleted} old log entries (retention: {$days} days)");
            }
        } catch (\Exception $e) {
            // Silent fail
        }
    }
}
