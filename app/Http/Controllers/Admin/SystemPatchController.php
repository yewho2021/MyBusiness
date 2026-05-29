<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Version;
use App\Models\VersionCode;
use App\Services\SqlParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemPatchController extends Controller
{
    /**
     * Show the System Patch page with version history.
     */
    public function index()
    {
        $history = [];
        $historyError = null;
        try {
            $history = Version::orderBy('version_code', 'desc')->limit(50)->get();
        } catch (\Exception $e) {
            $historyError = $e->getMessage();
            try {
                $history = DB::table('tbl_versions')
                    ->orderBy('version_code', 'desc')->limit(50)->get()
                    ->map(function ($row) {
                        $v = new Version();
                        $v->forceFill((array) $row);
                        $v->exists = true;
                        return $v;
                    });
                $historyError .= ' (raw fallback active)';
            } catch (\Exception $e2) {
                $historyError .= ' | Fallback: ' . $e2->getMessage();
            }
        }

        $currentVersion = null;
        try {
            $currentVersion = Version::latest();
        } catch (\Exception $e) {}

        return response(view('admin.pages.system.patch', compact('history', 'currentVersion', 'historyError')))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('X-Accel-Expires', '0');
    }

    /**
     * Preview: list zip contents without applying.
     */
    public function preview(Request $request)
    {
        $request->validate(['patch_file' => 'required|file|mimes:zip|max:102400']);

        $file = $request->file('patch_file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        $zip = new \ZipArchive();
        $tmpPath = $file->getRealPath();

        if ($zip->open($tmpPath) !== true) {
            return response()->json(['success' => false, 'error' => 'Cannot open ZIP file.'], 422);
        }

        $basePath = base_path();
        $codeFiles = [];
        $sqlFiles = [];
        $skippedFiles = [];
        $totalSize = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $entryName = $stat['name'];
            if (str_ends_with($entryName, '/')) continue;

            // Skip description files — handled separately
            if (in_array($entryName, ['PATCH.md', 'README.md', 'CHANGELOG.md'])) continue;

            $size = $stat['size'];
            $totalSize += $size;

            // SQL file
            if (preg_match('/\.sql$/i', $entryName)) {
                $sqlContent = $zip->getFromIndex($i);
                $statements = [];
                $sqlValid = true;

                if ($sqlContent !== false) {
                    $parsed = SqlParser::splitSql($sqlContent);
                    foreach ($parsed as $idx => $stmt) {
                        $stmt = trim($stmt);
                        if (empty($stmt) || preg_match('/^--/', $stmt) || preg_match('/^\/\*.*\*\/$/s', $stmt)) continue;

                        $preview = SqlParser::statementPreview($stmt);
                        $stmtResult = ['num' => $idx + 1, 'preview' => $preview, 'status' => 'ok', 'error' => null];

                        try {
                            if (!preg_match('/^SET\s/i', $stmt)) {
                                $pdo = DB::connection()->getPdo();
                                $prepStmt = $pdo->prepare($stmt);
                                $prepStmt->closeCursor();
                            }
                        } catch (\Exception $e) {
                            $errMsg = $e->getMessage();
                            if (preg_match('/^CREATE\s+TABLE/i', $stmt) && stripos($errMsg, 'already exists') !== false) {
                                $stmtResult['status'] = 'warn';
                                $stmtResult['error'] = 'Table already exists';
                            } else {
                                $stmtResult['status'] = 'err';
                                $stmtResult['error'] = $errMsg;
                                $sqlValid = false;
                            }
                        }
                        $statements[] = $stmtResult;
                    }
                }

                $sqlFiles[] = [
                    'path' => $entryName, 'size' => $size, 'valid' => $sqlValid,
                    'statements' => $statements, 'stmt_count' => count($statements),
                    'err_count' => count(array_filter($statements, fn($s) => $s['status'] === 'err')),
                ];
                continue;
            }

            // Dangerous path check
            if ($this->isDangerousPath($entryName)) {
                $skippedFiles[] = ['path' => $entryName, 'reason' => 'Blocked — sensitive or dangerous path'];
                continue;
            }

            $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
            if (in_array($ext, ['exe', 'dll', 'so', 'bin', 'dmg', 'iso'])) {
                $skippedFiles[] = ['path' => $entryName, 'reason' => 'Blocked — binary executable'];
                continue;
            }

            $targetPath = $basePath . '/' . $entryName;
            $exists = file_exists($targetPath);

            $codeFiles[] = [
                'path' => $entryName, 'size' => $size, 'exists' => $exists,
                'dir_exists' => is_dir(dirname($targetPath)),
                'action' => $exists ? 'overwrite' : (is_dir(dirname($targetPath)) ? 'create' : 'create (new dir)'),
            ];
        }

        $zip->close();

        // Read PATCH.md / README.md if present (patch description → changelog)
        $patchDescription = null;
        $zip2 = new \ZipArchive();
        if ($zip2->open($tmpPath) === true) {
            foreach (['PATCH.md', 'README.md', 'CHANGELOG.md'] as $descFile) {
                $content = $zip2->getFromName($descFile);
                if ($content !== false && trim($content) !== '') {
                    $patchDescription = trim($content);
                    break;
                }
            }
            $zip2->close();
        }

        // Check for duplicate patches
        $previousApply = null;
        $fileHash = md5_file($tmpPath);
        try {
            $byHash = $fileHash ? Version::findByHash($fileHash) : null;
            $byName = Version::findByFileName($fileName);

            if ($byHash) {
                $previousApply = [
                    'type' => 'exact_match',
                    'message' => 'This exact patch was applied on ' . $byHash->applied_at->format('d M Y H:i') . ' as ' . $byHash->getDisplayCode(),
                ];
            } elseif ($byName) {
                $previousApply = [
                    'type' => 'name_match',
                    'message' => 'A patch with this filename was applied on ' . $byName->applied_at->format('d M Y H:i') . ' as ' . $byName->getDisplayCode() . ' (different content)',
                ];
            }
        } catch (\Exception $e) {}

        return response()->json([
            'success' => true, 'file_name' => $fileName,
            'file_size' => SqlParser::formatFileSize($fileSize), 'file_hash' => $fileHash,
            'total_entries' => count($codeFiles) + count($sqlFiles),
            'code_files' => $codeFiles, 'sql_files' => $sqlFiles,
            'skipped' => $skippedFiles, 'total_size' => SqlParser::formatFileSize($totalSize),
            'previous_apply' => $previousApply,
            'patch_description' => $patchDescription,
        ]);
    }

    /**
     * Apply patch with full file backup to tbl_version_code.
     */
    public function apply(Request $request)
    {
        $request->validate(['patch_file' => 'required|file|mimes:zip|max:102400']);
        set_time_limit(600);

        $file = $request->file('patch_file');
        $fileName = $file->getClientOriginalName();
        $tmpPath = $file->getRealPath();
        $fileHash = md5_file($tmpPath);
        $startTime = microtime(true);
        $admin = $request->attributes->get('admin');

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath) !== true) {
            return response()->json(['success' => false, 'error' => 'Cannot open ZIP file.'], 422);
        }

        $basePath = base_path();
        $log = [];
        $filesOk = 0; $filesErr = 0; $filesCreated = 0; $filesOverwritten = 0;
        $sqlOk = 0; $sqlErr = 0; $sqlEntries = [];
        $totalBackupBytes = 0;

        // Read PATCH.md if present (description → changelog)
        $patchDescription = null;
        foreach (['PATCH.md', 'README.md', 'CHANGELOG.md'] as $descFile) {
            $content = $zip->getFromName($descFile);
            if ($content !== false && trim($content) !== '') {
                $patchDescription = trim($content);
                break;
            }
        }

        // Create version record (status=pending until complete)
        $versionCode = Version::generateCode();
        $version = Version::create([
            'version_code' => $versionCode,
            'type' => 'patch',
            'file_name' => $fileName,
            'file_hash' => $fileHash,
            'description' => $patchDescription,
            'status' => 'partial',
            'admin_id' => $admin?->id,
            'admin_name' => $admin?->name ?? 'Unknown',
            'applied_at' => now(),
        ]);

        $log[] = ['type' => 'info', 'msg' => "Patch: {$fileName} → v{$versionCode}"];

        // ── Phase 1: Extract code files with backup ────────
        $log[] = ['type' => 'info', 'msg' => '── Phase 1: Code files (with backup) ──'];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $entryName = $stat['name'];
            if (str_ends_with($entryName, '/')) continue;

            // Skip description files — already read for version.description
            if (in_array($entryName, ['PATCH.md', 'README.md', 'CHANGELOG.md'])) continue;

            if (preg_match('/\.sql$/i', $entryName)) {
                $sqlEntries[] = $entryName;
                continue;
            }

            if ($this->isDangerousPath($entryName)) {
                $log[] = ['type' => 'warn', 'msg' => "Skipped: {$entryName}"];
                continue;
            }

            $targetPath = $basePath . '/' . $entryName;
            $targetDir = dirname($targetPath);

            try {
                // Read current file content (before overwrite)
                $contentBefore = null;
                $action = 'create';
                $existed = file_exists($targetPath);

                if ($existed) {
                    $contentBefore = file_get_contents($targetPath);
                    $action = 'overwrite';
                }

                // Create directory if needed
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                // Read new content from ZIP
                $contentAfter = $zip->getFromIndex($i);
                if ($contentAfter === false) {
                    throw new \Exception("Failed to read from ZIP");
                }

                // Write new file to disk
                $bytes = file_put_contents($targetPath, $contentAfter);
                if ($bytes === false) {
                    throw new \Exception("file_put_contents failed — permission denied or disk full");
                }

                // Post-write verification
                $written = file_get_contents($targetPath);
                if ($written === false || md5($written) !== md5($contentAfter)) {
                    throw new \Exception("Write verification failed — file on disk doesn't match patch content");
                }

                // Invalidate OPcache for this specific file
                if (function_exists('opcache_invalidate')) {
                    opcache_invalidate($targetPath, true);
                }

                // Store backup in database
                $entry = VersionCode::store($version->id, $entryName, $action, $contentBefore, $contentAfter);
                $totalBackupBytes += strlen($entry->content_before ?? '') + strlen($entry->content_after ?? '');

                if ($existed) {
                    $filesOverwritten++;
                    $log[] = ['type' => 'ok', 'msg' => "Overwritten: {$entryName}"];
                } else {
                    $filesCreated++;
                    $log[] = ['type' => 'ok', 'msg' => "Created: {$entryName}"];
                }
                $filesOk++;

            } catch (\Exception $e) {
                $filesErr++;
                $log[] = ['type' => 'err', 'msg' => "Failed: {$entryName}", 'error' => $e->getMessage()];
            }
        }

        // ── Phase 2: Execute SQL patches ────────────────
        if (!empty($sqlEntries)) {
            $log[] = ['type' => 'info', 'msg' => '── Phase 2: SQL patches ──'];
            sort($sqlEntries);

            foreach ($sqlEntries as $sqlEntry) {
                $log[] = ['type' => 'info', 'msg' => "SQL: {$sqlEntry}"];

                try {
                    $sqlContent = $zip->getFromName($sqlEntry);
                    if ($sqlContent === false) throw new \Exception("Cannot read SQL from ZIP");

                    // Store SQL in version_code for reference
                    VersionCode::store($version->id, $sqlEntry, 'sql', $sqlContent, $sqlContent);

                    // Save to patches directory
                    $patchesDir = base_path('database/patches');
                    if (is_dir($patchesDir)) {
                        @file_put_contents($patchesDir . '/' . basename($sqlEntry), $sqlContent);
                    }

                    $statements = SqlParser::splitSql($sqlContent);
                    $stmtOk = 0; $stmtErr = 0;
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');

                    foreach ($statements as $idx => $stmt) {
                        $stmt = trim($stmt);
                        if (empty($stmt) || preg_match('/^--/', $stmt) || preg_match('/^\/\*.*\*\/$/s', $stmt)) continue;

                        $preview = SqlParser::statementPreview($stmt);
                        try {
                            DB::unprepared($stmt);
                            $stmtOk++; $sqlOk++;
                            $log[] = ['type' => 'ok', 'msg' => "  #{$idx}: {$preview}"];
                        } catch (\Exception $e) {
                            $stmtErr++; $sqlErr++;
                            $log[] = ['type' => 'err', 'msg' => "  #{$idx}: {$preview}", 'error' => $e->getMessage()];
                        }
                    }

                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    $log[] = ['type' => $stmtErr > 0 ? 'warn' : 'ok',
                        'msg' => "  Result: {$stmtOk} OK" . ($stmtErr > 0 ? ", {$stmtErr} failed" : '')];

                } catch (\Exception $e) {
                    $sqlErr++;
                    $log[] = ['type' => 'err', 'msg' => "SQL failed: {$sqlEntry}", 'error' => $e->getMessage()];
                }
            }
        }

        $zip->close();

        // ── Auto-clear caches ──────────────────────────
        $cacheLog = $this->clearAllCaches();
        if (!empty($cacheLog)) {
            $log[] = ['type' => 'info', 'msg' => '── Cache cleared: ' . implode(' | ', $cacheLog) . ' ──'];
        }

        $elapsed = round((microtime(true) - $startTime) * 1000);
        $allOk = ($filesErr === 0 && $sqlErr === 0);

        $summary = $allOk ? "Patch applied → v{$versionCode}" : "Patch applied with errors → v{$versionCode}";
        $log[] = ['type' => $allOk ? 'ok' : 'warn',
            'msg' => "{$summary} — {$filesOk} files ({$filesOverwritten} overwritten, {$filesCreated} new)"
                . (!empty($sqlEntries) ? ", {$sqlOk} SQL" : '') . " in {$elapsed}ms"
        ];

        // Update version record
        $version->update([
            'code_files' => $filesOk + $filesErr,
            'sql_files' => count($sqlEntries),
            'files_created' => $filesCreated,
            'files_overwritten' => $filesOverwritten,
            'sql_ok' => $sqlOk, 'sql_err' => $sqlErr,
            'total_backup_bytes' => $totalBackupBytes,
            'status' => $allOk ? 'success' : 'partial',
            'elapsed_ms' => $elapsed,
            'log' => $log,
        ]);

        return response()->json([
            'success' => $allOk, 'summary' => $summary,
            'version_code' => $versionCode,
            'files_ok' => $filesOk, 'files_overwritten' => $filesOverwritten,
            'files_created' => $filesCreated, 'files_err' => $filesErr,
            'sql_ok' => $sqlOk, 'sql_err' => $sqlErr,
            'sql_count' => count($sqlEntries),
            'elapsed_ms' => $elapsed, 'log' => $log,
        ]);
    }

    // ══════════════════════════════════════════════════
    // VERSION HISTORY
    // ══════════════════════════════════════════════════

    /**
     * AJAX: Get version detail with file list.
     */
    public function versionDetail($code)
    {
        $version = Version::where('version_code', $code)->firstOrFail();
        $files = $version->files()->orderBy('action')->orderBy('file_path')->get();

        return response()->json([
            'version' => [
                'code' => $version->version_code,
                'display' => $version->getDisplayCode(),
                'type' => $version->type,
                'file_name' => $version->file_name,
                'description' => $version->description,
                'status' => $version->status,
                'applied_at' => $version->applied_at?->format('d M Y H:i:s'),
                'admin_name' => $version->admin_name,
                'elapsed_ms' => $version->elapsed_ms,
                'backup_size' => $version->getBackupSizeHuman(),
                'rollback_target' => $version->rollback_target_code ? 'v' . $version->rollback_target_code : null,
                'rollback_chain' => $version->rollback_chain,
                'can_restore' => $version->canRestore(),
            ],
            'files' => $files->map(fn($f) => [
                'id' => $f->id,
                'path' => $f->file_path,
                'action' => $f->action,
                'action_label' => $f->getActionLabel(),
                'size_before' => $f->getSizeBeforeHuman(),
                'size_after' => $f->getSizeAfterHuman(),
                'has_before' => $f->content_before !== null,
                'has_after' => $f->content_after !== null,
            ]),
        ]);
    }

    /**
     * AJAX: View stored file content.
     */
    public function viewFile($code, $fileId)
    {
        $version = Version::where('version_code', $code)->firstOrFail();
        $file = VersionCode::where('version_id', $version->id)->where('id', $fileId)->firstOrFail();

        return response()->json([
            'path' => $file->file_path,
            'action' => $file->action,
            'before' => $file->getContentBefore(),
            'after' => $file->getContentAfter(),
            'size_before' => $file->size_before,
            'size_after' => $file->size_after,
        ]);
    }

    /**
     * Download a version's "after" files as ZIP.
     */
    public function downloadVersion($code)
    {
        $version = Version::where('version_code', $code)->firstOrFail();
        $files = $version->files;

        $zipPath = storage_path('app/version_' . $code . '.zip');
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Cannot create ZIP');
        }

        foreach ($files as $file) {
            $content = $file->getContentAfter();
            if ($content !== null) {
                $zip->addFromString($file->file_path, $content);
            }
        }
        $zip->close();

        $downloadName = 'v' . $code . '_' . ($version->file_name ?? 'version') . '.zip';
        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }

    // ══════════════════════════════════════════════════
    // ROLLBACK
    // ══════════════════════════════════════════════════

    /**
     * AJAX: Calculate rollback plan.
     */
    public function rollbackPreview(Request $request)
    {
        $targetCode = $request->input('target_code');
        if (!$targetCode) {
            return response()->json(['success' => false, 'error' => 'Target version required.'], 400);
        }

        $targetVersion = Version::where('version_code', $targetCode)->where('status', 'success')->first();
        if (!$targetVersion) {
            return response()->json(['success' => false, 'error' => "Version v{$targetCode} not found or failed."], 404);
        }

        $currentVersion = Version::latest();
        if (!$currentVersion) {
            return response()->json(['success' => false, 'error' => 'No versions found.'], 400);
        }

        if ($targetCode >= $currentVersion->version_code) {
            return response()->json(['success' => false, 'error' => 'Target must be older than current version.'], 400);
        }

        // Get versions to undo (newest first)
        $chain = Version::getChainBetween($targetCode, $currentVersion->version_code);

        if ($chain->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No versions found between target and current.'], 400);
        }

        // Check all versions have code backups
        $missingBackups = [];
        foreach ($chain as $v) {
            if (!$v->codeFiles()->exists()) {
                $missingBackups[] = 'v' . $v->version_code;
            }
        }

        // Collect affected files and SQL warnings
        $affectedFiles = [];
        $sqlWarnings = [];
        $filesToDelete = 0;
        $filesToRestore = 0;

        foreach ($chain as $v) {
            foreach ($v->files as $file) {
                if ($file->action === 'sql') {
                    $sqlWarnings[] = ['version' => 'v' . $v->version_code, 'file' => $file->file_path];
                } else {
                    if (!isset($affectedFiles[$file->file_path])) {
                        $affectedFiles[$file->file_path] = [
                            'path' => $file->file_path,
                            'action' => $file->action === 'create' && $file->content_before === null ? 'delete' : 'restore',
                        ];
                    }
                }
            }
        }

        foreach ($affectedFiles as $af) {
            if ($af['action'] === 'delete') $filesToDelete++;
            else $filesToRestore++;
        }

        return response()->json([
            'success' => true,
            'current_version' => 'v' . $currentVersion->version_code,
            'target_version' => 'v' . $targetCode,
            'target_label' => $targetVersion->file_name ?? $targetVersion->description,
            'versions_to_undo' => $chain->map(fn($v) => [
                'code' => 'v' . $v->version_code,
                'type' => $v->type,
                'file_name' => $v->file_name ?? $v->description,
                'code_files' => $v->code_files,
            ]),
            'files_to_restore' => $filesToRestore,
            'files_to_delete' => $filesToDelete,
            'affected_files' => array_values($affectedFiles),
            'sql_warnings' => $sqlWarnings,
            'missing_backups' => $missingBackups,
            'can_proceed' => empty($missingBackups),
        ]);
    }

    /**
     * Execute rollback to a target version.
     */
    public function rollbackExecute(Request $request)
    {
        $targetCode = $request->input('target_code');
        if (!$targetCode) {
            return response()->json(['success' => false, 'error' => 'Target version required.'], 400);
        }

        set_time_limit(600);
        $startTime = microtime(true);
        $admin = $request->attributes->get('admin');
        $basePath = base_path();
        $log = [];

        $targetVersion = Version::where('version_code', $targetCode)->where('status', 'success')->first();
        $currentVersion = Version::latest();

        if (!$targetVersion || !$currentVersion || $targetCode >= $currentVersion->version_code) {
            return response()->json(['success' => false, 'error' => 'Invalid rollback target.'], 400);
        }

        $chain = Version::getChainBetween($targetCode, $currentVersion->version_code);
        if ($chain->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No versions to undo.'], 400);
        }

        // Create rollback version record
        $rollbackCode = Version::generateCode();
        $rollbackVersion = Version::create([
            'version_code' => $rollbackCode,
            'type' => 'rollback',
            'description' => 'Restored to v' . $targetCode,
            'rollback_target_code' => $targetCode,
            'rollback_from_code' => $currentVersion->version_code,
            'rollback_chain' => $chain->pluck('version_code')->toArray(),
            'status' => 'partial',
            'admin_id' => $admin?->id,
            'admin_name' => $admin?->name ?? 'Unknown',
            'applied_at' => now(),
        ]);

        $log[] = ['type' => 'info', 'msg' => "Rollback: v{$currentVersion->version_code} → v{$targetCode} (creating v{$rollbackCode})"];

        $processedFiles = []; // track: file_path → {disk_before, final_content}
        $filesRestored = 0;
        $filesDeleted = 0;
        $totalBackupBytes = 0;
        $errors = 0;

        // ── Undo each version in reverse order ──────────
        foreach ($chain as $version) {
            $log[] = ['type' => 'info', 'msg' => "── Undoing v{$version->version_code}: {$version->file_name} ──"];

            $codeFiles = $version->files()->where('action', '!=', 'sql')->get();

            foreach ($codeFiles as $file) {
                $diskPath = $basePath . '/' . $file->file_path;

                try {
                    // First time seeing this file? Backup current disk state
                    if (!isset($processedFiles[$file->file_path])) {
                        $processedFiles[$file->file_path] = [
                            'disk_before' => file_exists($diskPath) ? file_get_contents($diskPath) : null,
                            'final_content' => null,
                            'final_action' => 'overwrite',
                        ];
                    }

                    // Restore based on action
                    if ($file->action === 'create' && $file->content_before === null) {
                        // File was CREATED by this version — delete it
                        if (file_exists($diskPath)) {
                            @unlink($diskPath);
                        }
                        $processedFiles[$file->file_path]['final_content'] = null;
                        $processedFiles[$file->file_path]['final_action'] = 'create'; // rollback record: we "created" a deletion
                        $filesDeleted++;
                        $log[] = ['type' => 'ok', 'msg' => "  Deleted: {$file->file_path}"];
                    } else {
                        // File was OVERWRITTEN — restore content_before
                        $restored = $file->getContentBefore();
                        if ($restored === null) {
                            throw new \Exception("No backup content for {$file->file_path}");
                        }

                        $dir = dirname($diskPath);
                        if (!is_dir($dir)) mkdir($dir, 0755, true);
                        file_put_contents($diskPath, $restored);

                        $processedFiles[$file->file_path]['final_content'] = $restored;
                        $processedFiles[$file->file_path]['final_action'] = 'overwrite';
                        $filesRestored++;
                        $log[] = ['type' => 'ok', 'msg' => "  Restored: {$file->file_path}"];
                    }

                } catch (\Exception $e) {
                    $errors++;
                    $log[] = ['type' => 'err', 'msg' => "  Failed: {$file->file_path}", 'error' => $e->getMessage()];
                }
            }

            // Log SQL warnings
            $sqlFiles = $version->files()->where('action', 'sql')->get();
            foreach ($sqlFiles as $sf) {
                $log[] = ['type' => 'warn', 'msg' => "  ⚠ SQL not auto-rolled back: {$sf->file_path}"];
            }
        }

        // ── Store rollback version's file records ──────
        foreach ($processedFiles as $path => $data) {
            $entry = VersionCode::store(
                $rollbackVersion->id,
                $path,
                $data['final_action'],
                $data['disk_before'],
                $data['final_content']
            );
            $totalBackupBytes += strlen($entry->content_before ?? '') + strlen($entry->content_after ?? '');
        }

        // ── Clear caches ──────────────────────────────
        $cacheLog = $this->clearAllCaches();
        if (!empty($cacheLog)) {
            $log[] = ['type' => 'info', 'msg' => '── Cache cleared: ' . implode(' | ', $cacheLog) . ' ──'];
        }

        $elapsed = round((microtime(true) - $startTime) * 1000);
        $allOk = $errors === 0;

        $summary = $allOk
            ? "Restored to v{$targetCode} → created v{$rollbackCode}"
            : "Restore completed with {$errors} error(s) → v{$rollbackCode}";

        $log[] = ['type' => $allOk ? 'ok' : 'warn',
            'msg' => "{$summary} — {$filesRestored} restored, {$filesDeleted} deleted in {$elapsed}ms"];

        $rollbackVersion->update([
            'code_files' => $filesRestored + $filesDeleted,
            'files_restored' => $filesRestored,
            'files_deleted' => $filesDeleted,
            'total_backup_bytes' => $totalBackupBytes,
            'status' => $allOk ? 'success' : 'partial',
            'elapsed_ms' => $elapsed,
            'log' => $log,
        ]);

        return response()->json([
            'success' => $allOk,
            'summary' => $summary,
            'version_code' => $rollbackCode,
            'files_restored' => $filesRestored,
            'files_deleted' => $filesDeleted,
            'errors' => $errors,
            'elapsed_ms' => $elapsed,
            'log' => $log,
        ]);
    }

    // ══════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════

    protected function isDangerousPath(string $path): bool
    {
        if (str_contains($path, '..')) return true;

        $allowed = [
            'app/', 'resources/views/', 'resources/css/', 'resources/js/',
            'public/css/', 'public/js/', 'public/images/',
            'database/patches/', 'database/seeders/', 'database/migrations/',
            'routes/', 'config/',
        ];

        foreach ($allowed as $dir) {
            if (str_starts_with($path, $dir)) {
                if (str_starts_with($path, 'public/') && str_ends_with(strtolower($path), '.php')) {
                    return true;
                }
                return false;
            }
        }

        // Allow root-level .sql and .md files
        if (preg_match('/^[^\/]+\.(sql|md)$/i', $path)) return false;

        return true;
    }

    /**
     * Clear all caches after patch/rollback.
     */
    protected function clearAllCaches(): array
    {
        $cacheLog = [];

        // Blade views
        $viewPath = storage_path('framework/views');
        if (is_dir($viewPath)) {
            $c = 0;
            foreach (glob($viewPath . '/*.php') as $f) { @unlink($f); $c++; }
            if ($c) $cacheLog[] = "Views: {$c}";
        }

        // App cache
        $cachePath = storage_path('framework/cache/data');
        if (!is_dir($cachePath)) $cachePath = storage_path('framework/cache');
        if (is_dir($cachePath)) {
            $c = 0;
            $iter = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cachePath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iter as $item) {
                if ($item->isFile() && $item->getFilename() !== '.gitignore') { @unlink($item); $c++; }
            }
            if ($c) $cacheLog[] = "Cache: {$c}";
        }

        // Config model cache
        try { \App\Models\Configuration::clearCache(); } catch (\Exception $e) {}

        // Bootstrap cache
        $bootstrapCache = base_path('bootstrap/cache');
        foreach (['config.php', 'routes-v7.php', 'events.php'] as $cf) {
            $f = $bootstrapCache . '/' . $cf;
            if (file_exists($f)) { @unlink($f); $cacheLog[] = $cf; }
        }

        // OPcache
        if (function_exists('opcache_reset')) {
            @opcache_reset();
            $cacheLog[] = 'OPcache';
        }

        // DB-based cache table
        try {
            $deleted = DB::table('cache')->where('key', 'LIKE', '%')->delete();
            if ($deleted) $cacheLog[] = "DB cache: {$deleted}";
        } catch (\Exception $e) {}

        return $cacheLog;
    }

}
