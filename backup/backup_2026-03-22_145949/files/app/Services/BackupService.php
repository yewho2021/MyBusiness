<?php

namespace App\Services;

use App\Models\BackupJob;
use App\Models\BackupRun;
use App\Models\BackupLog;
use App\Models\Changelog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class BackupService
{
    protected BackupRun $run;
    protected string $backupBasePath;

    public function __construct()
    {
        // Default base path — overridden per-run by resolveBasePath()
        $this->backupBasePath = base_path('backup');
    }

    /**
     * Resolve backup base path from run/job config, ensure directory exists
     */
    protected function resolveBasePath(?BackupRun $run = null): string
    {
        if ($run && !empty($run->destination_path)) {
            $path = str_starts_with($run->destination_path, '/')
                ? $run->destination_path
                : base_path($run->destination_path);
        } elseif ($run && $run->job) {
            $path = $run->job->getDestinationPath();
        } else {
            $path = base_path('backup');
        }

        $path = rtrim($path, '/');

        if (!File::isDirectory($path)) {
            // Check parent directory is writable before attempting
            $parentDir = dirname($path);
            if (!File::isDirectory($parentDir)) {
                throw new \Exception(
                    "Backup destination parent directory does not exist: {$parentDir}\n" .
                    "Attempted destination: {$path}\n" .
                    "Tip: Create the parent directory first, or use a path under an existing directory."
                );
            }
            if (!is_writable($parentDir)) {
                throw new \Exception(
                    "Backup destination parent directory is not writable: {$parentDir}\n" .
                    "Attempted destination: {$path}\n" .
                    "Current PHP user: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user()) . "\n" .
                    "Parent permissions: " . substr(sprintf('%o', fileperms($parentDir)), -4) . "\n" .
                    "Tip: Run chmod 755 {$parentDir} or chown to the web server user."
                );
            }
            try {
                File::makeDirectory($path, 0755, true);
            } catch (\Exception $e) {
                throw new \Exception(
                    "Failed to create backup destination: {$path}\n" .
                    "Error: " . $e->getMessage() . "\n" .
                    "Parent dir: {$parentDir} (writable: " . (is_writable($parentDir) ? 'yes' : 'NO') . ")\n" .
                    "PHP user: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user())
                );
            }
        } elseif (!is_writable($path)) {
            throw new \Exception(
                "Backup destination exists but is not writable: {$path}\n" .
                "Permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "\n" .
                "Owner: " . (function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($path))['name'] : fileowner($path)) . "\n" .
                "PHP user: " . (function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user())
            );
        }

        return $path;
    }

    /**
     * Execute a backup run
     */
    public function execute(BackupRun $run): void
    {
        $this->run = $run;

        try {
            // ── Resolve destination path ─────────────────────
            $this->backupBasePath = $this->resolveBasePath($run);
            $folderName = 'backup_' . now()->format('Y-m-d_His');
            $backupPath = $this->backupBasePath . '/' . $folderName;

            // Log path resolution immediately
            $this->log('info', 'Destination resolved: ' . $this->backupBasePath);

            // ── Identify New Changelog Entries ────────────────
        $lastSuccessfulBackup = BackupRun::where('status', BackupRun::STATUS_COMPLETED)
            ->where('id', '!=', $run->id)
            ->orderBy('completed_at', 'desc')
            ->first();

        $changelogQuery = Changelog::orderBy('created_at', 'asc');
        if ($lastSuccessfulBackup && $lastSuccessfulBackup->completed_at) {
            $changelogQuery->where('created_at', '>', $lastSuccessfulBackup->completed_at);
        }

        $newChanges = $changelogQuery->get();
        if ($newChanges->count() > 0) {
            $summary = "Contains " . $newChanges->count() . " update(s): " .
                $newChanges->map(fn($c) => "{$c->version} ({$c->title})")->implode(', ');
            $run->description = substr($summary, 0, 1000); // Guard length
        } else {
            $run->description = "No new changes since last backup.";
        }
        $run->save();

            // Update run status
            $run->update([
                'folder_name' => $folderName,
                'status' => BackupRun::STATUS_RUNNING,
                'started_at' => now(),
            ]);

            // Create backup directory
            try {
                if (!File::isDirectory($backupPath)) {
                    File::makeDirectory($backupPath, 0755, true);
                }
                File::makeDirectory($backupPath . '/files', 0755, true);
            } catch (\Exception $e) {
                throw new \Exception(
                    "Failed to create backup folder.\n" .
                    "Path: {$backupPath}\n" .
                    "Base: {$this->backupBasePath} (exists: " . (File::isDirectory($this->backupBasePath) ? 'yes' : 'NO') . ", writable: " . (is_writable($this->backupBasePath) ? 'yes' : 'NO') . ")\n" .
                    "Error: " . $e->getMessage()
                );
            }

            // ── Log Job Details ──────────────────────────────
            $this->log('info', '════════════════════════════════════════════');
            $this->log('info', 'BACKUP STARTED');
            $this->log('info', '════════════════════════════════════════════');
            $this->log('info', 'Folder: ' . $folderName);
            $this->log('info', 'Job: ' . ($run->job ? $run->job->name : 'Manual Backup'));
            if ($run->job) {
                $this->log('info', 'Frequency: ' . ucfirst($run->job->frequency));
                $this->log('info', 'Retention: Keep last ' . $run->job->retention_count . ' backups');
            }
            $this->log('info', 'Database: ' . ($run->include_database ? 'Yes' : 'No'));
            $this->log('info', 'Base Path: ' . base_path());
            $this->log('info', 'Backup Path: ' . $backupPath);

            // Log include/exclude paths
            $includePaths = $run->include_paths ?? [];
            $excludePaths = $run->exclude_paths ?? [];
            $excludeExtensions = $run->exclude_extensions ?? [];

            if (!empty($includePaths)) {
                $this->log('info', 'Include Paths: ' . implode(', ', $includePaths));
            } else {
                $this->log('info', 'Include Paths: [default] app, config, database, resources, routes, storage/app, public');
            }
            if (!empty($excludePaths)) {
                $this->log('info', 'Exclude Paths: ' . implode(', ', $excludePaths));
            }
            if (!empty($excludeExtensions)) {
                $this->log('info', 'Exclude Extensions: ' . implode(', ', $excludeExtensions));
            }
            $this->log('info', '────────────────────────────────────────────');

            // ── Phase 1: Scan Files ──────────────────────────
            $this->log('info', '[Phase 1/3] Scanning files...');

            // Debug: check which paths exist
            $resolvedIncludes = empty($includePaths)
                ? ['app', 'config', 'database', 'resources', 'routes', 'storage/app', 'public']
                : $includePaths;

            foreach ($resolvedIncludes as $path) {
                $cleanPath = trim($path, '/');
                if (str_starts_with($cleanPath, 'home/') || str_starts_with($cleanPath, '/')) {
                    $fullPath = '/' . ltrim($cleanPath, '/');
                } else {
                    $fullPath = base_path() . '/' . $cleanPath;
                }
                if (file_exists($fullPath)) {
                    $type = is_dir($fullPath) ? 'DIR' : 'FILE';
                    $this->log('info', "  [{$type}] {$path} → found");
                } else {
                    $this->log('warning', "  [MISSING] {$path} → not found at {$fullPath}");
                }
            }

            $files = $this->collectFiles($includePaths, $excludePaths, $excludeExtensions);
            $totalFiles = count($files);

            // Add 1 for DB dump if included
            $totalCount = $totalFiles + ($run->include_database ? 1 : 0);
            $run->update(['total_files' => $totalCount]);

            $this->log('info', "Scan complete: {$totalFiles} files found");
            $this->log('info', '────────────────────────────────────────────');

            // ── Phase 2: Copy Files ──────────────────────────
            if ($totalFiles > 0) {
                $this->log('info', '[Phase 2/3] Copying files...');
            } else {
                $this->log('warning', '[Phase 2/3] No files to copy — skipping');
            }

            $processed = 0;
            $totalSize = 0;
            $currentDir = '';
            $dirFileCount = 0;
            $dirSize = 0;
            $failedFiles = 0;
            $lastLoggedPct = -1;

            foreach ($files as $file) {
                try {
                    $relativePath = $this->getRelativePath($file);
                    $destPath = $backupPath . '/files/' . $relativePath;
                    $destDir = dirname($destPath);

                    if (!File::isDirectory($destDir)) {
                        File::makeDirectory($destDir, 0755, true);
                    }

                    $fileSize = filesize($file);
                    copy($file, $destPath);
                    $totalSize += $fileSize;
                    $processed++;

                    // Track directory-level summary
                    $fileDir = dirname($relativePath);
                    if ($fileDir === '.')
                        $fileDir = '/';

                    if ($fileDir !== $currentDir) {
                        if ($currentDir !== '' && $dirFileCount > 0) {
                            $pct = $totalCount > 0 ? (int) round(($processed / $totalCount) * 100) : 0;
                            $this->log('info', "[{$pct}%] {$currentDir}/ — {$dirFileCount} files (" . $this->formatBytes($dirSize) . ")");
                        }
                        $currentDir = $fileDir;
                        $dirFileCount = 0;
                        $dirSize = 0;
                    }
                    $dirFileCount++;
                    $dirSize += $fileSize;

                    // Update DB progress every 20 files (more frequent for live UI)
                    if ($processed % 20 === 0 || $processed === $totalFiles) {
                        $run->update([
                            'processed_files' => $processed,
                            'total_size' => $totalSize,
                        ]);
                    }

                    // Log milestone every 5% so UI always shows movement
                    $currentPct = $totalCount > 0 ? (int) floor(($processed / $totalCount) * 100) : 0;
                    if ($currentPct >= $lastLoggedPct + 5 && $currentPct > $lastLoggedPct) {
                        // Flush current dir summary at milestone
                        if ($dirFileCount > 0) {
                            $this->log('info', "[{$currentPct}%] {$currentDir}/ — {$dirFileCount} files (" . $this->formatBytes($dirSize) . ")");
                            $dirFileCount = 0;
                            $dirSize = 0;
                        }
                        $this->log('info', "── Progress: {$currentPct}% — {$processed}/{$totalFiles} files, " . $this->formatBytes($totalSize));
                        $lastLoggedPct = $currentPct;
                    }
                } catch (\Exception $e) {
                    $failedFiles++;
                    $this->log('error', "FAILED: {$file} — " . $e->getMessage());
                }
            }

            // Log last directory
            if ($currentDir !== '' && $dirFileCount > 0) {
                $pct = $totalCount > 0 ? (int) round(($processed / $totalCount) * 100) : 0;
                $this->log('info', "[{$pct}%] {$currentDir}/ — {$dirFileCount} files (" . $this->formatBytes($dirSize) . ")");
            }

            if ($totalFiles > 0) {
                $this->log('success', "Files copied: {$processed}/{$totalFiles} (" . $this->formatBytes($totalSize) . ")" . ($failedFiles > 0 ? " — {$failedFiles} failed" : ''));
                $this->log('info', '────────────────────────────────────────────');
            }

            // ── Phase 3: Database Dump ───────────────────────
            if ($run->include_database) {
                $this->log('info', '[Phase 3/3] Dumping database...');
                $dbName = config('database.connections.mysql.database');
                $this->log('info', "Database: {$dbName}");
                $this->dumpDatabase($backupPath);
                $processed++;
                $run->update(['processed_files' => $processed]);

                $dbFile = $backupPath . '/database.sql';
                if (File::exists($dbFile)) {
                    $dbSize = filesize($dbFile);
                    $this->log('success', 'Database dump completed (' . $this->formatBytes($dbSize) . ')');
                } else {
                    $this->log('warning', 'Database dump file not created');
                }
                $this->log('info', '────────────────────────────────────────────');
            } else {
                $this->log('info', '[Phase 3/3] Database dump — skipped (not included)');
            }

            // ── Phase 4: File Manifest ───────────────────────
            $this->log('info', 'Saving file manifest...');
            $manifest = array_map(fn($f) => $this->getRelativePath($f), $files);
            sort($manifest);
            File::put($backupPath . '/file_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
            $this->log('info', 'File manifest saved: ' . count($manifest) . ' entries');

            // ── Phase 5: Metadata ────────────────────────────
            $metadata = [
                'backup_id' => $run->id,
                'job_id' => $run->job_id,
                'job_name' => $run->job ? $run->job->name : 'Manual',
                'folder_name' => $folderName,
                'created_at' => now()->toIso8601String(),
                'total_files' => $totalFiles,
                'total_size' => $totalSize,
                'manifest_count' => count($manifest),
                'include_paths' => $run->include_paths,
                'exclude_paths' => $run->exclude_paths,
                'include_database' => $run->include_database,
                'description' => $run->description,
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
            ];
            File::put($backupPath . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));

            // ── Complete ─────────────────────────────────────
            $run->update([
                'status' => BackupRun::STATUS_COMPLETED,
                'total_size' => $totalSize,
                'completed_at' => now(),
            ]);

            $duration = abs($run->started_at->diffInSeconds(now()));
            $this->log('info', '════════════════════════════════════════════');
            $this->log('success', 'BACKUP COMPLETED SUCCESSFULLY');
            $this->log('info', "Files: {$totalFiles} | Size: " . $this->formatBytes($totalSize) . " | Duration: {$duration}s");
            $this->log('info', 'Manifest: ' . count($manifest) . ' tracked files');
            $this->log('info', '════════════════════════════════════════════');

            // Update job
            if ($run->job_id) {
                $job = BackupJob::find($run->job_id);
                if ($job) {
                    $job->update(['last_run_at' => now()]);
                    $this->enforceRetention($job);
                }
            }

        } catch (\Exception $e) {
            $run->update([
                'status' => BackupRun::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            $this->log('error', '════════════════════════════════════════════');
            $this->log('error', 'BACKUP FAILED: ' . $e->getMessage());
            $this->log('error', 'File: ' . $e->getFile() . ':' . $e->getLine());
            $this->log('error', '════════════════════════════════════════════');
            throw $e;
        }
    }

    /**
     * Restore from a backup run — Enterprise 5-Phase Restore
     */
    public function restore(BackupRun $run): void
    {
        $this->run = $run;

        try {
            $this->backupBasePath = $this->resolveBasePath($run);
            $backupPath = $run->getBackupPath();

            $run->update(['status' => BackupRun::STATUS_RESTORING, 'started_at' => now()]);

            $this->log('info', '════════════════════════════════════════════');
            $this->log('info', 'ENTERPRISE RESTORE STARTED');
            $this->log('info', '════════════════════════════════════════════');
            $this->log('info', 'Restoring from: ' . $run->folder_name);

            // ══════════════════════════════════════════════════
            // PHASE 0: PRE-FLIGHT CHECK
            // ══════════════════════════════════════════════════
            $this->log('info', '────────────────────────────────────────────');
            $this->log('info', '[Phase 0/5] Pre-flight checks...');

            if (!File::isDirectory($backupPath)) {
                throw new \Exception("Backup folder not found: {$backupPath}");
            }
            $this->log('info', '  ✓ Backup folder exists');

            $filesPath = $backupPath . '/files';
            $hasFiles = File::isDirectory($filesPath);
            $this->log('info', '  ' . ($hasFiles ? '✓' : '✗') . ' Files directory: ' . ($hasFiles ? 'found' : 'not found'));

            $dbDump = $backupPath . '/database.sql';
            $hasDatabase = File::exists($dbDump);
            $this->log('info', '  ' . ($hasDatabase ? '✓' : '✗') . ' Database dump: ' . ($hasDatabase ? 'found (' . $this->formatBytes(filesize($dbDump)) . ')' : 'not found'));

            $manifestFile = $backupPath . '/file_manifest.json';
            $hasManifest = File::exists($manifestFile);
            $manifest = $hasManifest ? json_decode(File::get($manifestFile), true) : [];
            $this->log('info', '  ' . ($hasManifest ? '✓' : '⚠') . ' File manifest: ' . ($hasManifest ? count($manifest) . ' entries' : 'not found (legacy backup — extra files will NOT be cleaned)'));

            if (!$hasFiles && !$hasDatabase) {
                throw new \Exception('Backup contains no files and no database dump.');
            }

            $this->log('info', '  Pre-flight: PASSED');

            // Count total work items for progress
            $backupFiles = $hasFiles ? $this->getAllFilesInDir($filesPath) : [];
            $totalWork = count($backupFiles) + ($hasDatabase ? 1 : 0);
            $run->update(['total_files' => $totalWork, 'processed_files' => 0]);

            // ══════════════════════════════════════════════════
            // PHASE 1: SAFETY SNAPSHOT
            // ══════════════════════════════════════════════════
            $this->log('info', '────────────────────────────────────────────');
            $this->log('info', '[Phase 1/5] Creating safety snapshot...');
            $this->log('info', '  This allows recovery if restore fails.');

            $safetyFolder = 'safety_' . now()->format('Y-m-d_His');
            $safetyPath = $this->backupBasePath . '/' . $safetyFolder;
            File::makeDirectory($safetyPath, 0755, true);
            File::makeDirectory($safetyPath . '/files', 0755, true);

            // Create a record in the database so it shows up in dashboard
            $safetyRun = BackupRun::create([
                'job_id' => $run->job_id,
                'folder_name' => $safetyFolder,
                'status' => BackupRun::STATUS_RUNNING,
                'include_database' => $hasDatabase,
                'started_at' => now(),
            ]);

            // Quick snapshot of essential files only (not full backup)
            $essentialPaths = ['app', 'config', 'routes', 'resources/views'];
            $essentialCount = 0;
            $essentialFiles = [];
            foreach ($essentialPaths as $ep) {
                $fullEp = base_path($ep);
                if (!File::isDirectory($fullEp))
                    continue;
                $epFiles = $this->getAllFilesInDir($fullEp);
                foreach ($epFiles as $ef) {
                    $relPath = str_replace(base_path() . '/', '', $ef);
                    $destFile = $safetyPath . '/files/' . $relPath;
                    $destDir = dirname($destFile);
                    if (!File::isDirectory($destDir))
                        File::makeDirectory($destDir, 0755, true);
                    try {
                        copy($ef, $destFile);
                        $essentialCount++;
                        $essentialFiles[] = $relPath;
                    } catch (\Exception $e) {
                    }
                }
            }

            // Save manifest for safety snapshot
            File::put($safetyPath . '/file_manifest.json', json_encode($essentialFiles, JSON_PRETTY_PRINT));

            // Snapshot current database
            if ($hasDatabase) {
                $this->log('info', '  Snapshotting current database...');
                try {
                    $this->phpDatabaseDump($safetyPath . '/database.sql');
                    $this->log('info', '  ✓ Database snapshot saved');
                } catch (\Exception $e) {
                    $this->log('warning', '  ⚠ Database snapshot failed: ' . $e->getMessage());
                }
            }

            // Mark safety snapshot as completed
            $safetyRun->update([
                'status' => BackupRun::STATUS_COMPLETED,
                'total_files' => $essentialCount + ($hasDatabase ? 1 : 0),
                'processed_files' => $essentialCount + ($hasDatabase ? 1 : 0),
                'total_size' => $this->getDirSize($safetyPath),
                'completed_at' => now(),
            ]);

            $this->log('success', "  Safety snapshot created and tracked: {$safetyFolder} ({$essentialCount} files)");

            // ══════════════════════════════════════════════════
            // PHASE 2: MAINTENANCE MODE
            // ══════════════════════════════════════════════════
            $this->log('info', '────────────────────────────────────────────');
            $this->log('info', '[Phase 2/5] Enabling maintenance mode...');

            $secretToken = bin2hex(random_bytes(16));
            try {
                \Artisan::call('down', ['--secret' => $secretToken]);
                $this->log('info', '  ✓ Maintenance mode ON');
                $this->log('info', '  Secret bypass token: ' . $secretToken);
            } catch (\Exception $e) {
                $this->log('warning', '  ⚠ Could not enable maintenance mode: ' . $e->getMessage());
            }

            // ══════════════════════════════════════════════════
            // PHASE 3: DATABASE RESTORE (FIRST)
            // ══════════════════════════════════════════════════
            $processed = 0;

            if ($hasDatabase) {
                $this->log('info', '────────────────────────────────────────────');
                $this->log('info', '[Phase 3/5] Restoring database...');
                $this->log('info', '  Database is restored FIRST for consistency.');

                // Save current sessions BEFORE database restore
                $savedSessions = [];
                try {
                    $savedSessions = DB::select('SELECT * FROM sessions');
                    $this->log('info', '  ✓ Saved ' . count($savedSessions) . ' active session(s)');
                } catch (\Exception $e) {
                    $this->log('warning', '  ⚠ Could not save sessions (table may not exist)');
                }

                // Save current backup_runs and backup_logs for THIS run
                $currentRunData = null;
                $currentLogs = [];
                try {
                    $currentRunData = DB::selectOne('SELECT * FROM tbl_backup_runs WHERE id = ?', [$run->id]);
                    $currentLogs = DB::select('SELECT * FROM tbl_backup_logs WHERE run_id = ?', [$run->id]);
                    $this->log('info', '  ✓ Saved current restore progress (' . count($currentLogs) . ' log entries)');
                } catch (\Exception $e) {
                    $this->log('warning', '  ⚠ Could not save restore state');
                }

                // Restore the database
                $this->log('info', '  Importing SQL dump...');
                $this->restoreDatabase($dbDump);
                $this->log('success', '  ✓ Database import completed');

                // Re-inject sessions so admin stays logged in
                try {
                    if (!empty($savedSessions)) {
                        DB::statement('DELETE FROM sessions');
                        foreach ($savedSessions as $session) {
                            $sessionArr = (array) $session;
                            DB::table('sessions')->insert($sessionArr);
                        }
                        $this->log('info', '  ✓ Sessions re-injected (' . count($savedSessions) . ' sessions)');
                    }
                } catch (\Exception $e) {
                    $this->log('warning', '  ⚠ Session re-injection failed: ' . $e->getMessage());
                }

                // Re-inject backup run state so logs keep working
                try {
                    if ($currentRunData) {
                        $runArr = (array) $currentRunData;
                        $runArr['status'] = 'restoring';
                        DB::table('tbl_backup_runs')->updateOrInsert(
                            ['id' => $run->id],
                            $runArr
                        );
                    }
                    if (!empty($currentLogs)) {
                        foreach ($currentLogs as $log) {
                            $logArr = (array) $log;
                            DB::table('tbl_backup_logs')->insertOrIgnore($logArr);
                        }
                        $this->log('info', '  ✓ Restore progress state preserved');
                    }
                } catch (\Exception $e) {
                    $this->log('warning', '  ⚠ Could not preserve restore state: ' . $e->getMessage());
                }

                $processed++;
                $run->update(['processed_files' => $processed]);
            } else {
                $this->log('info', '[Phase 3/5] Database restore — skipped (no dump in backup)');
            }

            // ══════════════════════════════════════════════════
            // PHASE 4: FILE RESTORE + CLEANUP
            // ══════════════════════════════════════════════════
            if ($hasFiles) {
                $this->log('info', '────────────────────────────────────────────');
                $this->log('info', '[Phase 4/5] Restoring files...');

                $total = count($backupFiles);
                $fileProcessed = 0;
                $currentDir = '';
                $dirFileCount = 0;

                foreach ($backupFiles as $file) {
                    try {
                        $relativePath = str_replace($filesPath . '/', '', $file);
                        // Normalize path separators
                        $relativePath = str_replace('\\', '/', $relativePath);
                        $destPath = base_path($relativePath);
                        $destDir = dirname($destPath);

                        if (!File::isDirectory($destDir)) {
                            File::makeDirectory($destDir, 0755, true);
                        }

                        copy($file, $destPath);
                        $fileProcessed++;
                        $processed++;

                        // Track directory-level summary
                        $fileDir = dirname($relativePath);
                        if ($fileDir === '.')
                            $fileDir = '/';
                        if ($fileDir !== $currentDir) {
                            if ($currentDir !== '' && $dirFileCount > 0) {
                                $pct = $totalWork > 0 ? (int) round(($processed / $totalWork) * 100) : 0;
                                $this->log('info', "  [{$pct}%] {$currentDir}/ — {$dirFileCount} files");
                            }
                            $currentDir = $fileDir;
                            $dirFileCount = 0;
                        }
                        $dirFileCount++;

                        if ($fileProcessed % 20 === 0 || $fileProcessed === $total) {
                            $run->update(['processed_files' => $processed]);
                        }
                    } catch (\Exception $e) {
                        $this->log('warning', "  ✗ Failed: {$file} — " . $e->getMessage());
                    }
                }

                // Log last directory
                if ($currentDir !== '' && $dirFileCount > 0) {
                    $pct = $totalWork > 0 ? (int) round(($processed / $totalWork) * 100) : 0;
                    $this->log('info', "  [{$pct}%] {$currentDir}/ — {$dirFileCount} files");
                }

                $this->log('success', "  Files restored: {$fileProcessed}/{$total}");

                // ── CLEANUP: DELETE EXTRA FILES ──────────────
                if ($hasManifest && !empty($manifest)) {
                    $this->log('info', '  Cleaning up extra files not in backup...');

                    // Protected paths — never delete these
                    $protectedPaths = ['vendor/', 'node_modules/', 'backup/', '.env', 'storage/logs/', 'storage/framework/', '.git/'];

                    // Scan current files in the same include paths
                    $includePaths = $run->include_paths ?? [];
                    if (empty($includePaths)) {
                        $includePaths = ['app', 'config', 'database', 'resources', 'routes', 'public'];
                    }

                    $currentFiles = [];
                    foreach ($includePaths as $ip) {
                        $ip = trim($ip, '/');
                        if (str_starts_with($ip, 'home/') || str_starts_with($ip, '/')) {
                            $fullIp = '/' . ltrim($ip, '/');
                        } else {
                            $fullIp = base_path($ip);
                        }
                        if (is_dir($fullIp)) {
                            $ipFiles = $this->getAllFilesInDir($fullIp);
                            foreach ($ipFiles as $cf) {
                                $currentFiles[] = str_replace(base_path() . '/', '', $cf);
                            }
                        }
                    }

                    // Find files that exist NOW but were NOT in the backup
                    $manifestFlipped = array_flip($manifest);
                    $deletedCount = 0;
                    $skippedCount = 0;

                    foreach ($currentFiles as $currentFile) {
                        // Normalize
                        $currentFile = str_replace('\\', '/', $currentFile);

                        if (isset($manifestFlipped[$currentFile])) {
                            continue; // File is in manifest, keep it
                        }

                        // Check protected paths
                        $isProtected = false;
                        foreach ($protectedPaths as $pp) {
                            if (str_starts_with($currentFile, $pp) || $currentFile === rtrim($pp, '/')) {
                                $isProtected = true;
                                break;
                            }
                        }

                        if ($isProtected) {
                            $skippedCount++;
                            continue;
                        }

                        // Delete the extra file
                        $fullDeletePath = base_path($currentFile);
                        try {
                            if (File::exists($fullDeletePath)) {
                                File::delete($fullDeletePath);
                                $deletedCount++;
                            }
                        } catch (\Exception $e) {
                            $this->log('warning', "  ✗ Could not delete: {$currentFile}");
                        }
                    }

                    if ($deletedCount > 0) {
                        $this->log('success', "  Cleaned up: {$deletedCount} extra file(s) removed");
                    } else {
                        $this->log('info', '  No extra files to clean up');
                    }
                    if ($skippedCount > 0) {
                        $this->log('info', "  Skipped {$skippedCount} protected file(s)");
                    }
                } else {
                    $this->log('warning', '  ⚠ No file manifest — skipping extra file cleanup (legacy backup)');
                }
            } else {
                $this->log('info', '[Phase 4/5] File restore — skipped (no files in backup)');
            }

            // ══════════════════════════════════════════════════
            // PHASE 5: POST-RESTORE
            // ══════════════════════════════════════════════════
            $this->log('info', '────────────────────────────────────────────');
            $this->log('info', '[Phase 5/5] Post-restore cleanup...');

            // Clear Laravel caches
            try {
                \Artisan::call('cache:clear');
                $this->log('info', '  ✓ Application cache cleared');
            } catch (\Exception $e) {
                $this->log('warning', '  ⚠ Cache clear failed: ' . $e->getMessage());
            }

            try {
                \Artisan::call('config:clear');
                $this->log('info', '  ✓ Config cache cleared');
            } catch (\Exception $e) {
            }

            try {
                \Artisan::call('route:clear');
                $this->log('info', '  ✓ Route cache cleared');
            } catch (\Exception $e) {
            }

            try {
                \Artisan::call('view:clear');
                $this->log('info', '  ✓ View cache cleared');
            } catch (\Exception $e) {
            }

            // Exit maintenance mode
            try {
                \Artisan::call('up');
                $this->log('info', '  ✓ Maintenance mode OFF — site is live');
            } catch (\Exception $e) {
                $this->log('warning', '  ⚠ Could not exit maintenance mode. Run: php artisan up');
            }

            // ── COMPLETE ─────────────────────────────────────
            $run->update([
                'status' => BackupRun::STATUS_RESTORED,
                'completed_at' => now(),
            ]);

            $duration = $run->started_at ? abs($run->started_at->diffInSeconds(now())) : 0;
            $this->log('info', '════════════════════════════════════════════');
            $this->log('success', 'RESTORE COMPLETED SUCCESSFULLY');
            $this->log('info', 'Duration: ' . $duration . 's');
            $this->log('info', 'Safety snapshot: ' . $safetyFolder);
            $this->log('info', '════════════════════════════════════════════');

        } catch (\Exception $e) {
            // Try to exit maintenance mode even on failure
            try {
                \Artisan::call('up');
            } catch (\Exception $ex) {
            }

            $run->update([
                'status' => BackupRun::STATUS_FAILED,
                'error_message' => 'Restore failed: ' . $e->getMessage(),
                'completed_at' => now(),
            ]);
            $this->log('error', '════════════════════════════════════════════');
            $this->log('error', 'RESTORE FAILED: ' . $e->getMessage());
            $this->log('error', 'Safety snapshot available at: backup/' . ($safetyFolder ?? 'unknown'));
            $this->log('error', '════════════════════════════════════════════');
            throw $e;
        }
    }

    /**
     * Collect files based on include/exclude paths and extension filters
     */
    protected function collectFiles(array $includePaths, array $excludePaths, array $excludeExtensions = []): array
    {
        $basePath = base_path();
        $files = [];

        if (empty($includePaths)) {
            $includePaths = ['app', 'config', 'database', 'resources', 'routes', 'storage/app', 'public'];
        }

        $defaultExcludes = ['vendor', 'node_modules', 'backup', '.git', 'storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views'];
        $allExcludes = array_unique(array_merge($defaultExcludes, $excludePaths));

        // Normalize extensions (remove dots, lowercase)
        $excludeExts = array_map(fn($e) => strtolower(ltrim(trim($e), '.')), $excludeExtensions);

        foreach ($includePaths as $includePath) {
            $includePath = trim($includePath, '/');

            if (str_starts_with($includePath, 'home/') || str_starts_with($includePath, '/')) {
                $fullPath = '/' . ltrim($includePath, '/');
            } else {
                $fullPath = $basePath . '/' . $includePath;
            }

            if (!file_exists($fullPath))
                continue;

            if (is_file($fullPath)) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                if (!$this->isExcluded($fullPath, $allExcludes, $basePath) && !in_array($ext, $excludeExts)) {
                    $files[] = $fullPath;
                }
                continue;
            }

            if (is_dir($fullPath)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $ext = strtolower($file->getExtension());
                        if (!$this->isExcluded($file->getPathname(), $allExcludes, $basePath) && !in_array($ext, $excludeExts)) {
                            $files[] = $file->getPathname();
                        }
                    }
                }
            }
        }

        return $files;
    }

    /**
     * Check if a file path matches any exclude pattern
     */
    protected function isExcluded(string $filePath, array $excludePaths, string $basePath): bool
    {
        $relativePath = str_replace($basePath . '/', '', $filePath);
        foreach ($excludePaths as $exclude) {
            $exclude = trim($exclude, '/');
            if (str_starts_with($relativePath, $exclude . '/') || $relativePath === $exclude) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get relative path from base_path
     */
    protected function getRelativePath(string $filePath): string
    {
        return str_replace(base_path() . '/', '', $filePath);
    }

    /**
     * Get all files in a directory recursively
     */
    protected function getAllFilesInDir(string $dir): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    /**
     * Dump the MySQL database (PHP-only, no shell commands)
     */
    protected function dumpDatabase(string $backupPath): void
    {
        $dumpFile = $backupPath . '/database.sql';
        $this->phpDatabaseDump($dumpFile);

        if (File::exists($dumpFile)) {
            $this->run->update([
                'total_size' => ($this->run->total_size ?? 0) + filesize($dumpFile),
            ]);
        }
    }

    /**
     * PHP-based database dump fallback (when mysqldump not available)
     */
    protected function phpDatabaseDump(string $dumpFile): void
    {
        $tables = DB::select('SHOW TABLES');
        $sql = "-- Database Backup\n-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\nSET time_zone = '+00:00';\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $tableArray = (array) $table;
            $tableName = reset($tableArray);

            // Get CREATE TABLE
            $createResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (!empty($createResult)) {
                $createStmt = $createResult[0]->{'Create Table'} ?? '';
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createStmt . ";\n\n";
            }

            // Get data
            $rows = DB::select("SELECT * FROM `{$tableName}`");
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $values = array_map(function ($val) {
                        if (is_null($val))
                            return 'NULL';
                        return "'" . addslashes($val) . "'";
                    }, (array) $row);
                    $sql .= "INSERT INTO `{$tableName}` VALUES(" . implode(',', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        File::put($dumpFile, $sql);
    }

    /**
     * Restore database from SQL dump (PHP-only, no shell commands)
     */
    protected function restoreDatabase(string $dumpFile): void
    {
        $sql = File::get($dumpFile);
        $statements = array_filter(array_map('trim', explode(";\n", $sql)));

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($statements as $statement) {
            $statement = trim($statement, "; \t\n\r");
            if (!empty($statement) && !str_starts_with($statement, '--') && !str_starts_with($statement, '/*')) {
                try {
                    DB::unprepared($statement);
                } catch (\Exception $e) {
                    $this->log('warning', "SQL failed: " . substr($statement, 0, 80));
                }
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Enforce retention policy - delete old backups
     */
    public function enforceRetention(BackupJob $job): void
    {
        $retentionCount = $job->retention_count ?? 10;
        $runs = BackupRun::where('job_id', $job->id)
            ->where('status', BackupRun::STATUS_COMPLETED)
            ->orderBy('created_at', 'desc')
            ->skip($retentionCount)
            ->take(100)
            ->get();

        foreach ($runs as $oldRun) {
            $this->deleteBackup($oldRun);
        }
    }

    /**
     * Delete a backup and its files
     */
    public function deleteBackup(BackupRun $run): void
    {
        $backupPath = $run->getBackupPath();
        if ($run->folder_name && File::isDirectory($backupPath)) {
            File::deleteDirectory($backupPath);
        }
        $run->logs()->delete();
        $run->delete();
    }

    /**
     * Log a message for the current run
     */
    protected function log(string $level, string $message, ?string $filePath = null, ?int $fileSize = null): void
    {
        BackupLog::create([
            'run_id' => $this->run->id,
            'level' => $level,
            'message' => $message,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'logged_at' => now(),
        ]);
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824)
            return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)
            return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)
            return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    protected function getDirSize(string $dir): int
    {
        if (!File::isDirectory($dir))
            return 0;
        $size = 0;
        foreach (File::allFiles($dir) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
}

