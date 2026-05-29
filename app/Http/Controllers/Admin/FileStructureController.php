<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\Configuration;

class FileStructureController extends Controller
{
    public function index()
    {
        $fileOutput = $this->generateFileStructure();
        $dbOutput = $this->generateDatabaseSchema();
        return view('admin.pages.system.file-structure', compact('fileOutput', 'dbOutput'));
    }

    public function generate()
    {
        $type = request('type', 'both');

        $data = [];
        if ($type === 'files' || $type === 'both') {
            $data['fileOutput'] = $this->generateFileStructure();
        }
        if ($type === 'db' || $type === 'both') {
            $data['dbOutput'] = $this->generateDatabaseSchema();
        }

        return response()->json($data);
    }

    /**
     * Export full database dump as .sql or .zip
     */
    public function exportDatabase(string $format)
    {
        if (!in_array($format, ['sql', 'zip'])) {
            abort(404);
        }

        set_time_limit(600);

        $dbName = config('database.connections.mysql.database');
        $timestamp = now()->format('Y-m-d_His');
        $sqlFilename = "{$dbName}_{$timestamp}.sql";
        $sqlPath = storage_path("app/{$sqlFilename}");

        // ── Generate SQL dump ───────────────────────────
        $handle = fopen($sqlPath, 'w');
        if (!$handle) {
            abort(500, 'Cannot write to storage directory.');
        }

        // Header
        fwrite($handle, "-- =============================================\n");
        fwrite($handle, "-- Database Export: {$dbName}\n");
        fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n");
        fwrite($handle, "-- Server: " . config('database.connections.mysql.host') . "\n");
        fwrite($handle, "-- =============================================\n\n");
        fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET time_zone='+00:00';\n");
        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $table) {
            $arr = (array) $table;
            $tableName = reset($arr);

            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table: `{$tableName}`\n");
            fwrite($handle, "-- --------------------------------------------------------\n\n");

            // Structure
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            $createResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (!empty($createResult)) {
                fwrite($handle, $createResult[0]->{'Create Table'} . ";\n\n");
            }

            // Data (chunked)
            $offset = 0;
            $chunkSize = 500;
            $hasData = false;

            while (true) {
                $rows = DB::select("SELECT * FROM `{$tableName}` LIMIT {$chunkSize} OFFSET {$offset}");
                if (empty($rows)) break;

                if (!$hasData) {
                    $cols = array_keys((array) $rows[0]);
                    $colList = '`' . implode('`, `', $cols) . '`';
                    $hasData = true;
                }

                fwrite($handle, "INSERT INTO `{$tableName}` ({$colList}) VALUES\n");
                $valueLines = [];
                foreach ($rows as $row) {
                    $vals = array_map(function ($v) {
                        if (is_null($v)) return 'NULL';
                        return "'" . addslashes((string) $v) . "'";
                    }, (array) $row);
                    $valueLines[] = '(' . implode(', ', $vals) . ')';
                }
                fwrite($handle, implode(",\n", $valueLines) . ";\n\n");

                $offset += $chunkSize;
            }
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        // ── Return .sql or wrap in .zip ─────────────────
        if ($format === 'sql') {
            return response()->download($sqlPath, $sqlFilename, [
                'Content-Type' => 'application/sql',
            ])->deleteFileAfterSend(true);
        }

        // ZIP
        $zipFilename = "{$dbName}_{$timestamp}.zip";
        $zipPath = storage_path("app/{$zipFilename}");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            @unlink($sqlPath);
            abort(500, 'Cannot create ZIP file.');
        }

        $zip->addFile($sqlPath, $sqlFilename);
        $zip->close();

        // Clean up .sql, return .zip
        @unlink($sqlPath);

        return response()->download($zipPath, $zipFilename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * AJAX: Build the project export zip and return a detailed log.
     * The zip is saved to storage — download via exportForAIDownload().
     *
     * Enhanced with full phase-level logging, timing, and error diagnostics.
     */
    public function exportForAI()
    {
        set_time_limit(600);

        $basePath = base_path();
        $timestamp = now()->format('Y-m-d_His');
        $appSlug = \Illuminate\Support\Str::slug(config('app.name', 'export'), '_');
        $zipFilename = "export_{$appSlug}_{$timestamp}.zip";
        $zipPath = storage_path("app/{$zipFilename}");

        $log = [];
        $fileCount = 0;
        $totalSize = 0;
        $errors = 0;
        $warnings = 0;
        $startTime = microtime(true);
        $phaseStart = $startTime;

        // ── Helper to add phase timing ──
        $phaseLog = function (string $type, string $msg, ?string $desc = null) use (&$log, &$phaseStart, &$errors, &$warnings) {
            $elapsed = round((microtime(true) - $phaseStart) * 1000);
            $entry = ['type' => $type, 'msg' => $msg, 'ms' => $elapsed];
            if ($desc) $entry['desc'] = $desc;
            $log[] = $entry;
            if ($type === 'err') $errors++;
            if ($type === 'warn') $warnings++;
        };

        // ════════════════════════════════════════════════════
        // PHASE 0: PRE-FLIGHT CHECKS
        // ════════════════════════════════════════════════════
        $log[] = ['type' => 'phase', 'msg' => 'Phase 0: Pre-flight checks'];
        $phaseStart = microtime(true);

        // Check storage directory
        $storageDir = storage_path('app');
        if (!is_dir($storageDir)) {
            return response()->json([
                'success' => false,
                'error' => "Storage directory does not exist: {$storageDir}",
                'log' => [['type' => 'err', 'msg' => "storage/app directory missing: {$storageDir}"]],
            ], 500);
        }
        if (!is_writable($storageDir)) {
            return response()->json([
                'success' => false,
                'error' => "Storage directory not writable: {$storageDir}",
                'log' => [['type' => 'err', 'msg' => "storage/app not writable. Permissions: " . substr(sprintf('%o', fileperms($storageDir)), -4)]],
            ], 500);
        }
        $phaseLog('ok', 'storage/app writable');

        // Check ZipArchive extension
        if (!class_exists('ZipArchive')) {
            return response()->json([
                'success' => false,
                'error' => 'PHP ZipArchive extension not installed.',
                'log' => [['type' => 'err', 'msg' => 'ZipArchive class not found. Install php-zip extension.']],
            ], 500);
        }
        $phaseLog('ok', 'ZipArchive extension available');

        // Check base path readable
        if (!is_readable($basePath)) {
            return response()->json([
                'success' => false,
                'error' => "Project base path not readable: {$basePath}",
                'log' => [['type' => 'err', 'msg' => "base_path() not readable: {$basePath}"]],
            ], 500);
        }
        $phaseLog('ok', "Base path: {$basePath}");

        // Check available disk space (need at least 50MB)
        $freeSpace = @disk_free_space($storageDir);
        if ($freeSpace !== false) {
            $freeMB = round($freeSpace / 1048576);
            if ($freeSpace < 52428800) { // 50MB
                $phaseLog('warn', "Low disk space: {$freeMB} MB free — export may fail");
            } else {
                $phaseLog('ok', "Disk space: {$freeMB} MB free");
            }
        }

        // ════════════════════════════════════════════════════
        // PHASE 1: CREATE ZIP
        // ════════════════════════════════════════════════════
        $log[] = ['type' => 'phase', 'msg' => 'Phase 1: Create ZIP archive'];
        $phaseStart = microtime(true);

        $zip = new \ZipArchive();
        $zipResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($zipResult !== true) {
            $zipErrors = [
                \ZipArchive::ER_EXISTS => 'File already exists',
                \ZipArchive::ER_INCONS => 'Inconsistent archive',
                \ZipArchive::ER_INVAL  => 'Invalid argument',
                \ZipArchive::ER_MEMORY => 'Out of memory',
                \ZipArchive::ER_NOENT  => 'No such file',
                \ZipArchive::ER_NOZIP  => 'Not a zip archive',
                \ZipArchive::ER_OPEN   => 'Cannot open file',
                \ZipArchive::ER_READ   => 'Read error',
                \ZipArchive::ER_SEEK   => 'Seek error',
            ];
            $errMsg = $zipErrors[$zipResult] ?? "Unknown error code: {$zipResult}";
            return response()->json([
                'success' => false,
                'error' => "Cannot create ZIP: {$errMsg}",
                'log' => [
                    ['type' => 'err', 'msg' => "ZipArchive::open() failed: {$errMsg}"],
                    ['type' => 'err', 'msg' => "Path: {$zipPath}"],
                    ['type' => 'err', 'msg' => "Dir writable: " . (is_writable(dirname($zipPath)) ? 'yes' : 'NO')],
                ],
            ], 500);
        }
        $phaseLog('ok', "ZIP created: {$zipFilename}");

        // ════════════════════════════════════════════════════
        // PHASE 2: SCAN & ADD PROJECT FILES
        // ════════════════════════════════════════════════════
        $log[] = ['type' => 'phase', 'msg' => 'Phase 2: Scan & add project files'];
        $phaseStart = microtime(true);

        $excludeDirs = [
            'vendor', 'node_modules', '.git',
            'storage/framework', 'storage/logs', 'storage/app', 'storage/debugbar',
            'backup', 'tests', 'public/build', 'public/hot',
            'cache',
        ];

        $skipExts = ['zip', 'gz', 'tar', 'sqlite', 'log', 'cache', 'exe', 'dll', 'so',
                     'ico', 'woff', 'woff2', 'ttf', 'eot', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp',
                     'mp3', 'mp4', 'avi', 'mov', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'pptx'];
        $skipFiles = ['.env', '.gitignore', '.gitattributes', 'composer.lock', 'package-lock.json',
                      'error_log', 'media_disk,', 'tbl_media,'];

        $dirStats = [];
        $skippedByExt = [];
        $skippedByDir = [];
        $scanErrors = [];

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            $scannedCount = 0;

            foreach ($iterator as $file) {
                $scannedCount++;
                if ($file->isDir()) continue;

                $filePath = $file->getRealPath();
                if (!$filePath) continue; // Broken symlink

                $relativePath = substr($filePath, strlen($basePath) + 1);
                if (!$relativePath) continue;

                // Skip excluded directories
                $skip = false;
                foreach ($excludeDirs as $exDir) {
                    if (str_starts_with($relativePath, $exDir . '/') || $relativePath === $exDir) {
                        $skip = true;
                        if (!isset($skippedByDir[$exDir])) $skippedByDir[$exDir] = 0;
                        $skippedByDir[$exDir]++;
                        break;
                    }
                }
                if ($skip) continue;

                // Skip by exact filename
                if (in_array(basename($relativePath), $skipFiles)) continue;

                // Skip by extension
                $ext = strtolower($file->getExtension());
                if (in_array($ext, $skipExts)) {
                    if (!isset($skippedByExt[$ext])) $skippedByExt[$ext] = 0;
                    $skippedByExt[$ext]++;
                    continue;
                }

                // Get top-level dir for grouping
                $topDir = strstr($relativePath, '/', true) ?: '(root)';
                if (!isset($dirStats[$topDir])) {
                    $dirStats[$topDir] = ['count' => 0, 'size' => 0];
                }

                try {
                    $size = $file->getSize();
                    if (!$zip->addFile($filePath, $relativePath)) {
                        $scanErrors[] = "addFile failed: {$relativePath}";
                        continue;
                    }
                    $dirStats[$topDir]['count']++;
                    $dirStats[$topDir]['size'] += $size;
                    $fileCount++;
                    $totalSize += $size;
                } catch (\Exception $fileErr) {
                    $scanErrors[] = "{$relativePath}: {$fileErr->getMessage()}";
                }
            }

            $phaseLog('ok', "Scanned {$scannedCount} filesystem entries");

        } catch (\Exception $e) {
            $phaseLog('err', 'Scan failed: ' . $e->getMessage(), 'File: ' . $e->getFile() . ':' . $e->getLine());
        }

        // Log per-directory breakdown
        ksort($dirStats);
        foreach ($dirStats as $dir => $stats) {
            $sizeStr = $this->formatExportSize($stats['size']);
            $log[] = ['type' => 'ok', 'msg' => "  {$dir}/ — {$stats['count']} files ({$sizeStr})"];
        }

        // Log skipped directories with file counts
        if (!empty($skippedByDir)) {
            $log[] = ['type' => 'info', 'msg' => '── Excluded directories ──'];
            foreach ($skippedByDir as $dir => $count) {
                $log[] = ['type' => 'skip', 'msg' => "  {$dir}/ — {$count} files skipped"];
            }
        }

        // Log skipped extensions
        if (!empty($skippedByExt)) {
            $topSkipped = array_slice($skippedByExt, 0, 8, true);
            arsort($topSkipped);
            $extSummary = implode(', ', array_map(fn($e, $c) => ".{$e}({$c})", array_keys($topSkipped), $topSkipped));
            $log[] = ['type' => 'skip', 'msg' => "  Skipped extensions: {$extSummary}"];
        }

        // Log file-level errors
        if (!empty($scanErrors)) {
            $log[] = ['type' => 'warn', 'msg' => '── File errors (' . count($scanErrors) . ') ──'];
            foreach (array_slice($scanErrors, 0, 10) as $err) {
                $log[] = ['type' => 'err', 'msg' => "  {$err}"];
            }
            if (count($scanErrors) > 10) {
                $log[] = ['type' => 'err', 'msg' => '  ... and ' . (count($scanErrors) - 10) . ' more errors'];
            }
        }

        $phaseLog('ok', "Added {$fileCount} project files ({$this->formatExportSize($totalSize)})");

        // ════════════════════════════════════════════════════
        // PHASE 3: GENERATE AI CONTEXT FILES
        // ════════════════════════════════════════════════════
        $log[] = ['type' => 'phase', 'msg' => 'Phase 3: Generate AI context files'];
        $phaseStart = microtime(true);

        // 3a: Database schema
        $phaseStart = microtime(true);
        try {
            $schemaText = $this->generateDatabaseSchema();
            $zip->addFromString('_AI_DATABASE_SCHEMA.txt', $schemaText);
            $fileCount++;
            $totalSize += strlen($schemaText);
            $phaseLog('ok', '_AI_DATABASE_SCHEMA.txt (' . $this->formatExportSize(strlen($schemaText)) . ')');
        } catch (\Exception $e) {
            $phaseLog('err', '_AI_DATABASE_SCHEMA.txt — FAILED', $e->getMessage());
        }

        // 3b: File structure
        $phaseStart = microtime(true);
        try {
            $fileTree = $this->generateFileStructure();
            $zip->addFromString('_AI_FILE_STRUCTURE.txt', $fileTree);
            $fileCount++;
            $totalSize += strlen($fileTree);
            $phaseLog('ok', '_AI_FILE_STRUCTURE.txt (' . $this->formatExportSize(strlen($fileTree)) . ')');
        } catch (\Exception $e) {
            $phaseLog('err', '_AI_FILE_STRUCTURE.txt — FAILED', $e->getMessage());
        }

        // 3c: Seed data
        $phaseStart = microtime(true);
        try {
            $seedData = $this->generateSeedData();
            $zip->addFromString('_AI_SEED_DATA.sql', $seedData);
            $fileCount++;
            $totalSize += strlen($seedData);
            $phaseLog('ok', '_AI_SEED_DATA.sql (' . $this->formatExportSize(strlen($seedData)) . ')', 'Menus, config, roles, changelog, admin users (redacted)');
        } catch (\Exception $e) {
            $phaseLog('err', '_AI_SEED_DATA.sql — FAILED', $e->getMessage());
        }

        // 3d: Module inventory
        $moduleInventory = '';
        $phaseStart = microtime(true);
        try {
            $moduleInventory = $this->generateModuleInventory();
            $phaseLog('ok', 'Module inventory generated');
        } catch (\Exception $e) {
            $phaseLog('err', 'Module inventory — FAILED', $e->getMessage());
        }

        // 3e: AI README manifest
        $phaseStart = microtime(true);
        try {
            $manifest = $this->generateAIManifest($zip, $moduleInventory);
            $zip->addFromString('_AI_README.md', $manifest);
            $fileCount++;
            $phaseLog('ok', '_AI_README.md');
        } catch (\Exception $e) {
            $phaseLog('err', '_AI_README.md — FAILED', $e->getMessage());
        }

        // ════════════════════════════════════════════════════
        // PHASE 4: FINALIZE ZIP
        // ════════════════════════════════════════════════════
        $log[] = ['type' => 'phase', 'msg' => 'Phase 4: Finalize ZIP'];
        $phaseStart = microtime(true);

        $closeResult = $zip->close();
        if (!$closeResult) {
            $phaseLog('err', 'ZipArchive::close() returned false — ZIP may be corrupted');
        }

        $elapsed = round((microtime(true) - $startTime) * 1000);
        $zipSize = file_exists($zipPath) ? filesize($zipPath) : 0;

        if ($zipSize === 0) {
            $phaseLog('err', 'ZIP file is 0 bytes — export failed silently');
            $log[] = ['type' => 'err', 'msg' => "Path: {$zipPath}"];
            $log[] = ['type' => 'err', 'msg' => 'exists: ' . (file_exists($zipPath) ? 'yes' : 'no')];
            return response()->json([
                'success' => false,
                'error' => 'ZIP file is empty (0 bytes). Check disk space and file permissions.',
                'log' => $log,
            ], 500);
        }

        $phaseLog('ok', "ZIP finalized: {$this->formatExportSize($zipSize)} compressed");

        // ════════════════════════════════════════════════════
        // SUMMARY
        // ════════════════════════════════════════════════════
        $log[] = ['type' => 'phase', 'msg' => '── Summary ──'];
        $log[] = ['type' => 'info', 'msg' => "Files: {$fileCount} | Uncompressed: {$this->formatExportSize($totalSize)} | Compressed: {$this->formatExportSize($zipSize)}"];
        $log[] = ['type' => 'info', 'msg' => "Compression ratio: " . ($totalSize > 0 ? round((1 - $zipSize / $totalSize) * 100) . '%' : 'N/A')];
        $log[] = ['type' => 'info', 'msg' => "Duration: {$elapsed}ms | Errors: {$errors} | Warnings: {$warnings}"];

        // Store filename in session for download
        session(['export_zip' => $zipFilename]);

        return response()->json([
            'success'     => true,
            'file_count'  => $fileCount,
            'total_size'  => $this->formatExportSize($totalSize),
            'zip_size'    => $this->formatExportSize($zipSize),
            'zip_name'    => $zipFilename,
            'elapsed_ms'  => $elapsed,
            'errors'      => $errors,
            'warnings'    => $warnings,
            'log'         => $log,
            'download_url'=> route('admin.file-structure.export-ai-download'),
        ]);
    }

    /**
     * Download the previously generated export zip.
     */
    public function exportForAIDownload()
    {
        $filename = session('export_zip');

        if (!$filename) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No export file found in session. Please generate a new export.',
                    'debug' => ['session_id' => session()->getId()],
                ], 404);
            }
            abort(404, 'No export file found. Please generate a new export.');
        }

        $path = storage_path("app/{$filename}");
        if (!file_exists($path)) {
            session()->forget('export_zip');
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Export file not found on disk. It may have been cleaned up.',
                    'debug' => ['expected_path' => $path, 'filename' => $filename],
                ], 404);
            }
            abort(404, 'Export file expired. Please generate a new export.');
        }

        $fileSize = filesize($path);
        if ($fileSize === 0) {
            session()->forget('export_zip');
            @unlink($path);
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Export file is empty (0 bytes). Please regenerate.',
                ], 500);
            }
            abort(500, 'Export file is empty. Please regenerate.');
        }

        session()->forget('export_zip');

        return response()->download($path, $filename, [
            'Content-Type' => 'application/zip',
            'Content-Length' => $fileSize,
        ])->deleteFileAfterSend(true);
    }

    protected function formatExportSize(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Generate AI-readable manifest describing what's in the export.
     */
    protected function generateAIManifest(\ZipArchive $zip, string $moduleInventory = ''): string
    {
        $dbName = config('database.connections.mysql.database');
        $tableCount = count(DB::select('SHOW TABLES'));

        $portalName = Configuration::get('portal_name', config('app.name', 'Admin Portal'));

        $lines = [];
        $lines[] = '# ' . $portalName . ' — AI Study Package';
        $lines[] = '';
        $lines[] = '**Generated:** ' . now()->toDateTimeString();
        $lines[] = '**Stack:** Laravel 11.x · PHP 8.2 · MySQL (InnoDB) · cPanel shared hosting';
        $lines[] = '**Database:** ' . $dbName . ' (' . $tableCount . ' tables, tbl_ prefix)';
        $lines[] = '**Auth:** Encrypted cookie-based auth with admin singleton per request — NOT Laravel Guard';
        $lines[] = '**Live URL:** ' . config('app.url', url('/'));
        $lines[] = '';
        $lines[] = '## What\'s Included';
        $lines[] = '';
        $lines[] = '| Path | Contents |';
        $lines[] = '|------|----------|';
        $lines[] = '| `app/` | Controllers, Models, Services, Middleware, Jobs, Providers |';
        $lines[] = '| `resources/views/` | All Blade templates (layouts, pages, partials) |';
        $lines[] = '| `routes/` | Route definitions (admin.php, web.php) |';
        $lines[] = '| `config/` | Application configuration files |';
        $lines[] = '| `database/patches/` | SQL deployment patches (schema + data changes) |';
        $lines[] = '| `database/migrations/` | Laravel migration files |';
        $lines[] = '| `database/seeders/` | Database seeders |';
        $lines[] = '| `bootstrap/app.php` | Middleware aliases and registration |';
        $lines[] = '| `.htaccess` | URL rewriting (removes /public from URLs) |';
        $lines[] = '| `composer.json` | PHP dependencies |';
        $lines[] = '| `DEPLOY_RULES.md` | Deployment rules and conventions |';
        $lines[] = '| `PATCH_FORMAT.md` | Patch zip format guide for Claude |';
        $lines[] = '| `_AI_DATABASE_SCHEMA.txt` | Full DB schema with columns, types, indexes, FK map |';
        $lines[] = '| `_AI_FILE_STRUCTURE.txt` | Complete project file tree |';
        $lines[] = '| `_AI_SEED_DATA.sql` | **Reference data**: menus, config keys, roles, permissions, changelog, admin users (passwords redacted) |';
        $lines[] = '| `_AI_README.md` | This file |';
        $lines[] = '';
        $lines[] = '## What\'s Excluded (and why)';
        $lines[] = '';
        $lines[] = '| Excluded | Reason |';
        $lines[] = '|----------|--------|';
        $lines[] = '| `vendor/` | Composer packages — committed on server, not needed for AI study |';
        $lines[] = '| `node_modules/` | NPM packages |';
        $lines[] = '| `backup/` | Old server backups (duplicated source code) |';
        $lines[] = '| `storage/` | Logs, cache, sessions, compiled views |';
        $lines[] = '| `tests/*.zip` | Dev session ZIP files |';
        $lines[] = '| `.env` | Contains APP_KEY, DB passwords, API keys |';
        $lines[] = '| `composer.lock` | Auto-generated |';
        $lines[] = '';
        $lines[] = '## Key Conventions';
        $lines[] = '';
        $lines[] = '- **Auth:** Encrypted cookie auth → use `$request->attributes->get(\'admin\')` for current admin object, `$request->attributes->get(\'admin_id\')` for ID. NEVER use `Auth::user()` or `$request->cookie(\'admin_id\')` directly.';
        $lines[] = '- **DB:** No `php artisan migrate` — use SQL patches in `database/patches/`';
        $lines[] = '- **Deploy:** No `composer install` on server — vendor is committed';
        $lines[] = '- **CSS:** Page-level, self-contained in `@push(\'styles\')`, hardcoded hex colors';
        $lines[] = '- **JS:** Native `fetch()` with CSRF token, no jQuery/Axios';
        $lines[] = '- **Routes:** All in `routes/admin.php`, named `admin.{module}.{action}`';
        $lines[] = '- **Tables:** All use `tbl_` prefix (e.g. `tbl_admin`, `tbl_configuration`)';
        $lines[] = '- **Patches:** Build as .zip following `PATCH_FORMAT.md`, apply via System Patch module';
        $lines[] = '- **tbl_admin_role_menu_access:** Columns are `role_id, menu_id, created_at, updated_at` — NO `has_access` column';
        $lines[] = '';

        // Module inventory
        if (!empty($moduleInventory)) {
            $lines[] = $moduleInventory;
        }

        $lines[] = '## How to Continue Development';
        $lines[] = '';
        $lines[] = '1. Upload this zip to Claude and say "Study this project, then build [feature]"';
        $lines[] = '2. Claude reads `_AI_README.md` first, then `DEPLOY_RULES.md` and `PATCH_FORMAT.md`';
        $lines[] = '3. Claude references `_AI_DATABASE_SCHEMA.txt` for table structures';
        $lines[] = '4. Claude references `_AI_SEED_DATA.sql` to understand existing menus, config keys, roles';
        $lines[] = '5. Claude builds a patch .zip following PATCH_FORMAT.md';
        $lines[] = '6. Apply the patch via the System Patch module at `/system-patch`';
        $lines[] = '';

        // File stats
        $fileCount = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (!str_ends_with($name, '/')) $fileCount++;
        }
        $lines[] = '---';
        $lines[] = "**Total files in this export:** {$fileCount}";

        return implode("\n", $lines);
    }

    /**
     * Generate INSERT statements for key reference tables (seed data).
     */
    protected function generateSeedData(): string
    {
        $lines = [];
        $lines[] = '-- =============================================';
        $lines[] = '-- SEED DATA — Key Reference Tables';
        $lines[] = '-- Generated: ' . now()->toDateTimeString();
        $lines[] = '-- Purpose: Let AI understand existing menu structure,';
        $lines[] = '-- config keys, roles, permissions, and version history.';
        $lines[] = '-- DO NOT import blindly — this is for READING, not executing.';
        $lines[] = '-- =============================================';
        $lines[] = '';

        // Tables to dump with their special handling
        $tables = [
            'tbl_admin_menu_groups' => ['label' => 'Menu Groups', 'exclude' => []],
            'tbl_admin_menus'      => ['label' => 'Menu Items', 'exclude' => []],
            'tbl_admin_roles'      => ['label' => 'Roles', 'exclude' => []],
            'tbl_admin_role_menu_access' => ['label' => 'Role → Menu Permissions', 'exclude' => []],
            'tbl_configuration'    => ['label' => 'Configuration Keys', 'exclude' => []],
            'tbl_changelog'        => ['label' => 'Changelog / Version History', 'exclude' => []],
            'tbl_admin'            => ['label' => 'Admin Users (passwords redacted)', 'exclude' => ['password', 'two_factor_secret', 'admin_session_id']],
            'tbl_database'         => ['label' => 'Database Connections (passwords redacted)', 'exclude' => ['password']],
        ];

        foreach ($tables as $table => $meta) {
            try {
                $rows = DB::select("SELECT * FROM `{$table}` ORDER BY 1");
                $count = count($rows);
                $lines[] = '-- ─── ' . $meta['label'] . " ({$table}) — {$count} rows ───";

                if ($count === 0) {
                    $lines[] = '-- (empty table)';
                    $lines[] = '';
                    continue;
                }

                // Get columns
                $firstRow = (array) $rows[0];
                $columns = array_keys($firstRow);

                foreach ($rows as $row) {
                    $rowArr = (array) $row;
                    $vals = [];
                    foreach ($columns as $col) {
                        $val = $rowArr[$col];
                        if (is_null($val)) {
                            $vals[] = 'NULL';
                        } elseif (in_array($col, $meta['exclude'])) {
                            $vals[] = "'[REDACTED]'";
                        } else {
                            $vals[] = "'" . addslashes((string) $val) . "'";
                        }
                    }
                    $colList = '`' . implode('`, `', $columns) . '`';
                    $valList = implode(', ', $vals);
                    $lines[] = "INSERT INTO `{$table}` ({$colList}) VALUES ({$valList});";
                }
                $lines[] = '';

            } catch (\Exception $e) {
                $lines[] = "-- ERROR dumping {$table}: " . $e->getMessage();
                $lines[] = '';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Generate module inventory from menus + routes.
     */
    protected function generateModuleInventory(): string
    {
        $lines = [];

        // Get all menus with groups
        try {
            $menus = DB::select("
                SELECT m.id, m.title, m.icon, m.route_name, m.permission_key, m.sort_order, m.is_active,
                       g.name as group_name, m.parent_id, m.level
                FROM tbl_admin_menus m
                LEFT JOIN tbl_admin_menu_groups g ON m.group_id = g.id
                ORDER BY g.sort_order, m.sort_order
            ");

            $lines[] = '## Module Inventory';
            $lines[] = '';
            $lines[] = '| # | Module | Route | Icon | Status |';
            $lines[] = '|---|--------|-------|------|--------|';

            $num = 0;
            foreach ($menus as $m) {
                $num++;
                $status = $m->is_active ? '✅ Active' : '❌ Disabled';
                $indent = $m->level > 1 ? '  └ ' : '';
                $lines[] = "| {$num} | {$indent}{$m->title} | `{$m->route_name}` | `{$m->icon}` | {$status} |";
            }
            $lines[] = '';

        } catch (\Exception $e) {
            $lines[] = '## Module Inventory';
            $lines[] = '';
            $lines[] = 'Error generating: ' . $e->getMessage();
            $lines[] = '';
        }

        // Get changelog as version history
        try {
            $versions = DB::select("
                SELECT version, title, details, DATE_FORMAT(created_at, '%Y-%m-%d') as date
                FROM tbl_changelog
                WHERE app_type = 'office'
                ORDER BY created_at DESC
                LIMIT 30
            ");

            $lines[] = '## Version History (Changelog)';
            $lines[] = '';
            $lines[] = '| Version | Title | Date |';
            $lines[] = '|---------|-------|------|';
            foreach ($versions as $v) {
                $lines[] = "| {$v->version} | {$v->title} | {$v->date} |";
            }
            $lines[] = '';

        } catch (\Exception $e) {
            // skip
        }

        // Get config groups summary
        try {
            $groups = DB::select("
                SELECT `group`, COUNT(*) as cnt,
                       GROUP_CONCAT(`key` ORDER BY sort_order SEPARATOR ', ') as keys_list
                FROM tbl_configuration
                WHERE is_active = 1
                GROUP BY `group`
                ORDER BY `group`
            ");

            $lines[] = '## Configuration Groups';
            $lines[] = '';
            $lines[] = '| Group | Keys | Sample Keys |';
            $lines[] = '|-------|------|-------------|';
            foreach ($groups as $g) {
                $keys = $g->keys_list;
                if (strlen($keys) > 80) $keys = substr($keys, 0, 77) . '...';
                $lines[] = "| `{$g->group}` | {$g->cnt} | {$keys} |";
            }
            $lines[] = '';

        } catch (\Exception $e) {
            // skip
        }

        // Get roles summary
        try {
            $roles = DB::select("SELECT id, name, slug, description FROM tbl_admin_roles ORDER BY id");
            $lines[] = '## Roles';
            $lines[] = '';
            $lines[] = '| ID | Name | Slug | Description |';
            $lines[] = '|----|------|------|-------------|';
            foreach ($roles as $r) {
                $lines[] = "| {$r->id} | {$r->name} | `{$r->slug}` | {$r->description} |";
            }
            $lines[] = '';
        } catch (\Exception $e) {
            // skip
        }

        return implode("\n", $lines);
    }

    // ─── FILE STRUCTURE ──────────────────────────────
    protected function generateFileStructure(): string
    {
        $basePath = base_path();
        $lines = [];

        $lines[] = '=============================================';
        $lines[] = 'FILE STRUCTURE';
        $lines[] = 'Generated: ' . now()->format('D M d H:i:s T Y');
        $lines[] = 'Directory: ' . $basePath;
        $lines[] = '=============================================';
        $lines[] = '';
        $lines[] = basename($basePath) . '/';

        $tree = $this->buildTree($basePath);
        $this->renderTree($tree, '', $lines);

        $totalDirs = 0;
        $totalFiles = 0;
        $this->countItems($tree, $totalDirs, $totalFiles);

        $lines[] = '';
        $lines[] = "{$totalDirs} directories, {$totalFiles} files";
        $lines[] = '';
        $lines[] = '=============================================';

        return implode("\n", $lines);
    }

    // ─── DATABASE SCHEMA ─────────────────────────────
    protected function generateDatabaseSchema(): string
    {
        $lines = [];
        $dbName = config('database.connections.mysql.database');
        $dbHost = config('database.connections.mysql.host');

        $lines[] = '=============================================';
        $lines[] = 'DATABASE SCHEMA';
        $lines[] = 'Generated: ' . now()->format('D M d H:i:s T Y');
        $lines[] = '=============================================';
        $lines[] = '';
        $lines[] = "Database : {$dbName}";
        $lines[] = "Host     : {$dbHost}";

        try {
            $sizeResult = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables WHERE table_schema = ?
            ", [$dbName]);
            $lines[] = "Size     : " . ($sizeResult[0]->size_mb ?? '0') . " MB";

            $tableStatus = DB::select("
                SELECT table_name, table_rows, 
                       ROUND((data_length + index_length) / 1024, 1) AS size_kb, engine
                FROM information_schema.tables WHERE table_schema = ? ORDER BY table_name
            ", [$dbName]);

            $lines[] = "Tables   : " . count($tableStatus);
            $lines[] = '';

            // Overview
            $lines[] = '─────────────────────────────────────────────';
            $lines[] = 'TABLE OVERVIEW';
            $lines[] = '─────────────────────────────────────────────';
            $lines[] = sprintf('%-40s %10s %12s %10s', 'Table Name', 'Rows', 'Size (KB)', 'Engine');
            $lines[] = sprintf('%-40s %10s %12s %10s', str_repeat('─', 40), str_repeat('─', 10), str_repeat('─', 12), str_repeat('─', 10));

            foreach ($tableStatus as $t) {
                $lines[] = sprintf('%-40s %10s %12s %10s', $t->table_name, number_format($t->table_rows), $t->size_kb, $t->engine);
            }

            $lines[] = '';
            $lines[] = '─────────────────────────────────────────────';
            $lines[] = 'TABLE DETAILS (COLUMNS & INDEXES)';
            $lines[] = '─────────────────────────────────────────────';

            foreach ($tableStatus as $t) {
                $tableName = $t->table_name;
                $rowCount = 0;
                try {
                    $cnt = DB::select("SELECT COUNT(*) as cnt FROM `{$tableName}`");
                    $rowCount = $cnt[0]->cnt ?? 0;
                } catch (\Exception $e) {
                    $rowCount = $t->table_rows ?? 0;
                }

                $lines[] = '';
                $lines[] = '┌─────────────────────────────────────────────';
                $lines[] = "│ TABLE: {$tableName}  ({$rowCount} rows)";
                $lines[] = '├─────────────────────────────────────────────';
                $lines[] = '│';
                $lines[] = '│  Columns:';
                $lines[] = sprintf('│  %-30s %-25s %-8s %-10s %s', 'Field', 'Type', 'Null', 'Key', 'Default / Extra');
                $lines[] = sprintf('│  %-30s %-25s %-8s %-10s %s', str_repeat('─', 30), str_repeat('─', 25), str_repeat('─', 8), str_repeat('─', 10), str_repeat('─', 30));

                $columns = DB::select("
                    SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY,
                           IFNULL(COLUMN_DEFAULT, '') AS col_default, EXTRA
                    FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                    ORDER BY ORDINAL_POSITION
                ", [$dbName, $tableName]);

                foreach ($columns as $col) {
                    $detail = '';
                    if ($col->col_default !== '') $detail = 'default: ' . $col->col_default;
                    if ($col->EXTRA) $detail .= ($detail ? ', ' : '') . $col->EXTRA;
                    $lines[] = sprintf('│  %-30s %-25s %-8s %-10s %s',
                        $col->COLUMN_NAME, $col->COLUMN_TYPE, $col->IS_NULLABLE, $col->COLUMN_KEY, $detail);
                }

                // Indexes
                $indexes = DB::select("SHOW INDEX FROM `{$tableName}`");
                if (!empty($indexes)) {
                    $lines[] = '│';
                    $lines[] = '│  Indexes:';
                    $prevIdx = '';
                    foreach ($indexes as $idx) {
                        if ($idx->Key_name !== $prevIdx) {
                            $label = 'INDEX';
                            if ($idx->Key_name === 'PRIMARY') $label = 'PRIMARY KEY';
                            elseif ($idx->Non_unique == 0) $label = 'UNIQUE';
                            $lines[] = "│    - {$idx->Key_name} ({$label}): {$idx->Column_name}";
                        } else {
                            $lines[] = "│      + {$idx->Column_name}";
                        }
                        $prevIdx = $idx->Key_name;
                    }
                }

                $lines[] = '└─────────────────────────────────────────────';
            }

            // FK map
            $fks = DB::select("
                SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL
                ORDER BY TABLE_NAME
            ", [$dbName]);

            if (!empty($fks)) {
                $lines[] = '';
                $lines[] = '─────────────────────────────────────────────';
                $lines[] = 'RELATIONSHIPS MAP';
                $lines[] = '─────────────────────────────────────────────';
                foreach ($fks as $fk) {
                    $lines[] = "  {$fk->TABLE_NAME}.{$fk->COLUMN_NAME}  ──►  {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}";
                }
            }

        } catch (\Exception $e) {
            $lines[] = '';
            $lines[] = 'DATABASE ERROR: ' . $e->getMessage();
        }

        $lines[] = '';
        $lines[] = '=============================================';

        return implode("\n", $lines);
    }

    // ─── HELPERS ─────────────────────────────────────

    protected function buildTree(string $path): array
    {
        $tree = [];
        $excludeDirs = ['vendor', 'node_modules', '.git', 'storage/framework/cache', 'storage/framework/views', 'storage/framework/sessions'];

        try { $items = scandir($path); } catch (\Exception $e) { return $tree; }

        $dirs = [];
        $files = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $path . '/' . $item;
            $relativePath = str_replace(base_path() . '/', '', $fullPath);

            $skip = false;
            foreach ($excludeDirs as $exc) {
                if ($relativePath === $exc || str_starts_with($relativePath, $exc . '/')) { $skip = true; break; }
            }

            if (is_dir($fullPath)) {
                $dirs[$item] = $skip ? ['__skipped__' => true] : $this->buildTree($fullPath);
            } else {
                $files[] = $item;
            }
        }

        ksort($dirs);
        sort($files);

        foreach ($dirs as $name => $children) { $tree[$name . '/'] = $children; }
        foreach ($files as $file) { $tree[$file] = null; }

        return $tree;
    }

    protected function renderTree(array $tree, string $prefix, array &$lines): void
    {
        $keys = array_keys($tree);
        $total = count($keys);

        foreach ($keys as $i => $name) {
            $isLast = ($i === $total - 1);
            $connector = $isLast ? '└── ' : '├── ';
            $childPrefix = $isLast ? '    ' : '│   ';
            $children = $tree[$name];

            if ($children === null) {
                $lines[] = $prefix . $connector . $name;
            } elseif (isset($children['__skipped__'])) {
                $lines[] = $prefix . $connector . $name . ' (skipped)';
            } else {
                $lines[] = $prefix . $connector . $name;
                $this->renderTree($children, $prefix . $childPrefix, $lines);
            }
        }
    }

    protected function countItems(array $tree, int &$dirs, int &$files): void
    {
        foreach ($tree as $name => $children) {
            if ($children === null) { $files++; }
            else { $dirs++; if (!isset($children['__skipped__'])) $this->countItems($children, $dirs, $files); }
        }
    }
}
