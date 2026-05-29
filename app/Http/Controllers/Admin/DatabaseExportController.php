<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseConnection;
use App\Traits\DatabaseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controllers\HasMiddleware;

class DatabaseExportController extends Controller implements HasMiddleware
{
    use DatabaseHelpers;

    public static function middleware(): array
    {
        return static::adminOnlyMiddleware();
    }

    public function export(Request $request)
    {
        $conn = $this->db();
        if ($request->isMethod('post')) {
            $selectedTables = $request->input('tables', []);
            $includeStructure = $request->boolean('include_structure', true);
            $includeData = $request->boolean('include_data', true);
            set_time_limit(0); // Prevent timeout for large exports
            $dbName = $this->getActiveDbName();

            $filename = $dbName . '_' . now()->format('Y-m-d_His') . '.sql';
            $filepath = storage_path('app/' . $filename);

            // Open file for streaming output
            $handle = fopen($filepath, 'w');
            if (!$handle) {
                return back()->with('error', 'Cannot write export file. Check storage/app permissions.');
            }

            // Write Header
            fwrite($handle, "-- Database Export: {$dbName}\n-- Generated: " . now()->toDateTimeString() . "\n\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\nSET time_zone='+00:00';\nSET FOREIGN_KEY_CHECKS=0;\n\n");

            $tables = $conn->select('SHOW TABLES');
            foreach ($tables as $table) {
                $arr = (array) $table;
                $tn = reset($arr);
                if (!empty($selectedTables) && !in_array($tn, $selectedTables))
                    continue;

                fwrite($handle, "-- --------------------------------------------------------\n-- Table: `{$tn}`\n-- --------------------------------------------------------\n\n");

                if ($includeStructure) {
                    fwrite($handle, "DROP TABLE IF EXISTS `{$tn}`;\n");
                    $cr = $conn->select("SHOW CREATE TABLE `{$tn}`");
                    if (!empty($cr))
                        fwrite($handle, $cr[0]->{'Create Table'} . ";\n\n");
                }

                if ($includeData) {
                    $offset = 0;
                    $limit = 500; // Chunk size
                    while (true) {
                        $rows = $conn->select("SELECT * FROM `{$tn}` LIMIT {$limit} OFFSET {$offset}");
                        if (empty($rows)) {
                            break; // No more rows
                        }

                        $cols = array_keys((array) $rows[0]);
                        $colList = '`' . implode('`, `', $cols) . '`';

                        fwrite($handle, "INSERT INTO `{$tn}` ({$colList}) VALUES\n");
                        $vl = [];
                        foreach ($rows as $row) {
                            $vals = array_map(fn($v) => is_null($v) ? 'NULL' : $conn->getPdo()->quote((string) $v), (array) $row);
                            $vl[] = '(' . implode(', ', $vals) . ')';
                        }
                        fwrite($handle, implode(",\n", $vl) . ";\n\n");

                        $offset += $limit;
                    }
                }
            }
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            return response()->download($filepath, $filename)->deleteFileAfterSend(true);
        }
        $tables = $conn->select('SHOW TABLES');
        $dbName = $this->getActiveDbName();
        $tableNames = [];
        foreach ($tables as $t) {
            $a = (array) $t;
            $tableNames[] = reset($a);
        }
        return view('admin.pages.database.export', compact('tableNames', 'dbName'));
    }

    /**
     * AJAX: Build export with detailed log and optional ZIP.
     */
    public function exportAjax(Request $request)
    {
        set_time_limit(600);

        $conn = $this->db();
        $dbName = $this->getActiveDbName();
        $selectedTables = $request->input('tables', []);
        $includeStructure = $request->boolean('include_structure', true);
        $includeData = $request->boolean('include_data', true);
        $format = $request->input('format', 'sql'); // sql or zip

        $log = [];
        $startTime = microtime(true);
        $totalRows = 0;
        $tableCount = 0;

        // ── Phase 1: Validate ──
        $log[] = ['type' => 'phase', 'msg' => 'Phase 1: Validate'];
        $phaseStart = microtime(true);

        if (empty($selectedTables)) {
            return response()->json([
                'success' => false,
                'error' => 'No tables selected.',
                'log' => [['type' => 'err', 'msg' => 'No tables selected for export.']],
            ], 422);
        }

        // Verify tables exist
        $allTables = [];
        foreach ($conn->select('SHOW TABLES') as $t) {
            $a = (array) $t;
            $allTables[] = reset($a);
        }

        $validTables = array_intersect($selectedTables, $allTables);
        $invalidTables = array_diff($selectedTables, $allTables);

        if (!empty($invalidTables)) {
            foreach ($invalidTables as $inv) {
                $log[] = ['type' => 'warn', 'msg' => "Table not found: {$inv} — skipped"];
            }
        }

        if (empty($validTables)) {
            return response()->json([
                'success' => false,
                'error' => 'None of the selected tables exist.',
                'log' => $log,
            ], 422);
        }

        $log[] = ['type' => 'ok', 'msg' => count($validTables) . ' tables selected', 'ms' => round((microtime(true) - $phaseStart) * 1000)];

        // ── Phase 2: Generate SQL ──
        $log[] = ['type' => 'phase', 'msg' => 'Phase 2: Generate SQL'];

        $timestamp = now()->format('Y-m-d_His');
        $sqlFilename = "{$dbName}_{$timestamp}.sql";
        $sqlPath = storage_path("app/{$sqlFilename}");

        $handle = fopen($sqlPath, 'w');
        if (!$handle) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot write to storage directory.',
                'log' => [['type' => 'err', 'msg' => 'fopen() failed: ' . $sqlPath]],
            ], 500);
        }

        // SQL Header
        fwrite($handle, "-- =============================================\n");
        fwrite($handle, "-- Database Export: {$dbName}\n");
        fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n");
        fwrite($handle, "-- Tables: " . count($validTables) . "\n");
        fwrite($handle, "-- =============================================\n\n");
        fwrite($handle, "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\nSET time_zone='+00:00';\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

        foreach ($validTables as $tn) {
            $phaseStart = microtime(true);
            $tableRows = 0;
            $tableBytes = 0;

            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table: `{$tn}`\n");
            fwrite($handle, "-- --------------------------------------------------------\n\n");

            if ($includeStructure) {
                fwrite($handle, "DROP TABLE IF EXISTS `{$tn}`;\n");
                $cr = $conn->select("SHOW CREATE TABLE `{$tn}`");
                if (!empty($cr)) {
                    $createSql = $cr[0]->{'Create Table'} . ";\n\n";
                    fwrite($handle, $createSql);
                    $tableBytes += strlen($createSql);
                }
            }

            if ($includeData) {
                $offset = 0;
                $limit = 500;
                while (true) {
                    $rows = $conn->select("SELECT * FROM `{$tn}` LIMIT {$limit} OFFSET {$offset}");
                    if (empty($rows)) break;

                    $cols = array_keys((array) $rows[0]);
                    $colList = '`' . implode('`, `', $cols) . '`';

                    $insertSql = "INSERT INTO `{$tn}` ({$colList}) VALUES\n";
                    $vl = [];
                    foreach ($rows as $row) {
                        $vals = array_map(fn($v) => is_null($v) ? 'NULL' : $conn->getPdo()->quote((string) $v), (array) $row);
                        $vl[] = '(' . implode(', ', $vals) . ')';
                        $tableRows++;
                    }
                    $insertSql .= implode(",\n", $vl) . ";\n\n";
                    fwrite($handle, $insertSql);
                    $tableBytes += strlen($insertSql);

                    $offset += $limit;
                }
            }

            $totalRows += $tableRows;
            $tableCount++;
            $elapsed = round((microtime(true) - $phaseStart) * 1000);
            $sizeStr = $tableBytes >= 1048576 ? number_format($tableBytes / 1048576, 2) . ' MB' : number_format($tableBytes / 1024, 1) . ' KB';
            $log[] = ['type' => 'ok', 'msg' => "{$tn} — " . number_format($tableRows) . " rows ({$sizeStr})", 'ms' => $elapsed];
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        $sqlSize = filesize($sqlPath);

        // ── Phase 3: Finalize ──
        $log[] = ['type' => 'phase', 'msg' => 'Phase 3: Finalize'];
        $phaseStart = microtime(true);

        $downloadFilename = $sqlFilename;
        $downloadPath = $sqlPath;

        if ($format === 'zip') {
            $zipFilename = "{$dbName}_{$timestamp}.zip";
            $zipPath = storage_path("app/{$zipFilename}");

            $zip = new \ZipArchive();
            $zipResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if ($zipResult !== true) {
                @unlink($sqlPath);
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot create ZIP file.',
                    'log' => $log,
                ], 500);
            }

            $zip->addFile($sqlPath, $sqlFilename);
            $zip->close();
            @unlink($sqlPath); // Remove raw .sql

            $zipSize = filesize($zipPath);
            $ratio = $sqlSize > 0 ? round((1 - $zipSize / $sqlSize) * 100) : 0;
            $zipSizeStr = $zipSize >= 1048576 ? number_format($zipSize / 1048576, 2) . ' MB' : number_format($zipSize / 1024, 1) . ' KB';
            $sqlSizeStr = $sqlSize >= 1048576 ? number_format($sqlSize / 1048576, 2) . ' MB' : number_format($sqlSize / 1024, 1) . ' KB';
            $log[] = ['type' => 'ok', 'msg' => "ZIP: {$sqlSizeStr} → {$zipSizeStr} ({$ratio}%)", 'ms' => round((microtime(true) - $phaseStart) * 1000)];

            $downloadFilename = $zipFilename;
            $downloadPath = $zipPath;
            $finalSize = $zipSize;
        } else {
            $sqlSizeStr = $sqlSize >= 1048576 ? number_format($sqlSize / 1048576, 2) . ' MB' : number_format($sqlSize / 1024, 1) . ' KB';
            $log[] = ['type' => 'ok', 'msg' => "SQL file: {$sqlSizeStr}", 'ms' => round((microtime(true) - $phaseStart) * 1000)];
            $finalSize = $sqlSize;
        }

        // ── Summary ──
        $elapsed = round((microtime(true) - $startTime) * 1000);
        $finalSizeStr = $finalSize >= 1048576 ? number_format($finalSize / 1048576, 2) . ' MB' : number_format($finalSize / 1024, 1) . ' KB';

        $log[] = ['type' => 'phase', 'msg' => '── Summary ──'];
        $log[] = ['type' => 'info', 'msg' => "{$tableCount} tables · " . number_format($totalRows) . " rows · {$finalSizeStr} · {$elapsed}ms"];

        session(['db_export_file' => $downloadFilename]);

        return response()->json([
            'success' => true,
            'table_count' => $tableCount,
            'total_rows' => $totalRows,
            'file_size' => $finalSizeStr,
            'file_name' => $downloadFilename,
            'format' => $format,
            'elapsed_ms' => $elapsed,
            'log' => $log,
            'download_url' => route('admin.database.export-download'),
        ]);
    }

    /**
     * Download the previously generated export file.
     */
    public function exportDownload()
    {
        $filename = session('db_export_file');
        if (!$filename) {
            abort(404, 'No export file found. Please generate a new export.');
        }

        $path = storage_path("app/{$filename}");
        if (!file_exists($path)) {
            session()->forget('db_export_file');
            abort(404, 'Export file expired. Please generate a new export.');
        }

        session()->forget('db_export_file');

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $mime = $ext === 'zip' ? 'application/zip' : 'application/sql';

        return response()->download($path, $filename, [
            'Content-Type' => $mime,
        ])->deleteFileAfterSend(true);
    }

    /**
     * ER Diagram: return tables, columns, and relationships as JSON.
     */
    public function erDiagramData()
    {
        $conn = $this->db();
        $dbName = $this->getActiveDbName();

        // Get all tables
        $rawTables = $conn->select('SHOW TABLES');
        $tableNames = [];
        foreach ($rawTables as $t) {
            $a = (array) $t;
            $tableNames[] = reset($a);
        }

        // Get columns for each table
        $tables = [];
        foreach ($tableNames as $tn) {
            try {
                $cols = $conn->select("SHOW FULL COLUMNS FROM `{$tn}`");
                $columns = [];
                foreach ($cols as $col) {
                    $columns[] = [
                        'name'     => $col->Field,
                        'type'     => $col->Type,
                        'key'      => $col->Key,      // PRI, MUL, UNI, ''
                        'nullable' => $col->Null === 'YES',
                        'default'  => $col->Default,
                    ];
                }
                $tables[] = [
                    'name'    => $tn,
                    'columns' => $columns,
                ];
            } catch (\Exception $e) {
                // Skip tables we can't describe
                continue;
            }
        }

        // Get explicit foreign keys from information_schema
        $relations = [];
        try {
            $fks = $conn->select("
                SELECT
                    kcu.TABLE_NAME       AS from_table,
                    kcu.COLUMN_NAME      AS from_column,
                    kcu.REFERENCED_TABLE_NAME  AS to_table,
                    kcu.REFERENCED_COLUMN_NAME AS to_column
                FROM information_schema.KEY_COLUMN_USAGE kcu
                WHERE kcu.TABLE_SCHEMA = ?
                  AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                ORDER BY kcu.TABLE_NAME, kcu.ORDINAL_POSITION
            ", [$dbName]);

            foreach ($fks as $fk) {
                $relations[] = [
                    'from_table'  => $fk->from_table,
                    'from_column' => $fk->from_column,
                    'to_table'    => $fk->to_table,
                    'to_column'   => $fk->to_column,
                    'type'        => 'FK',
                ];
            }
        } catch (\Exception $e) {
            // information_schema may not be accessible on external DBs
        }

        // Infer relationships from naming conventions
        // e.g. column "role_id" → look for table "tbl_roles" or "roles" with "id" column
        $tableNameSet = array_flip($tableNames);
        $existingRelKeys = [];
        foreach ($relations as $r) {
            $existingRelKeys[$r['from_table'] . '.' . $r['from_column']] = true;
        }

        foreach ($tables as $tbl) {
            foreach ($tbl['columns'] as $col) {
                // Skip if already has an explicit FK
                if (isset($existingRelKeys[$tbl['name'] . '.' . $col['name']])) continue;

                // Match patterns: xxx_id, xxxId
                if (preg_match('/^(.+)_id$/i', $col['name'], $m)) {
                    $base = $m[1]; // e.g. "admin_role" from "admin_role_id"

                    // Try common table name patterns
                    $candidates = [
                        $base,                         // admin_role
                        $base . 's',                   // admin_roles
                        'tbl_' . $base,                // tbl_admin_role
                        'tbl_' . $base . 's',          // tbl_admin_roles
                    ];

                    foreach ($candidates as $candidate) {
                        if (isset($tableNameSet[$candidate]) && $candidate !== $tbl['name']) {
                            // Verify the target table has an 'id' column
                            $targetTable = collect($tables)->firstWhere('name', $candidate);
                            if ($targetTable) {
                                $hasId = collect($targetTable['columns'])->contains('name', 'id');
                                if ($hasId) {
                                    $relations[] = [
                                        'from_table'  => $tbl['name'],
                                        'from_column' => $col['name'],
                                        'to_table'    => $candidate,
                                        'to_column'   => 'id',
                                        'type'        => 'inferred',
                                    ];
                                    break; // Stop at first match
                                }
                            }
                        }
                    }
                }
            }
        }

        return response()->json([
            'database' => $dbName,
            'tables'   => $tables,
            'relations' => $relations,
        ]);
    }

    public function import(Request $request)
    {
        $conn = $this->db();

        // ── GET: show standalone import page ─────────────
        if ($request->isMethod('get')) {
            return view('admin.pages.database.import');
        }

        // ── POST: process import ─────────────────────────
        $request->validate(['sql_file' => 'required|file|max:51200']);
        set_time_limit(600);

        $file = $request->file('sql_file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $startTime = microtime(true);

        $log = [];  // detailed execution log
        $okCount = 0;
        $errCount = 0;
        $skipCount = 0;

        try {
            $rawSql = file_get_contents($file->getRealPath());

            // Detect encoding issues
            if (!mb_check_encoding($rawSql, 'UTF-8')) {
                $rawSql = mb_convert_encoding($rawSql, 'UTF-8', 'auto');
                $log[] = ['type' => 'warn', 'msg' => 'File encoding converted to UTF-8'];
            }

            $log[] = ['type' => 'info', 'msg' => "File: {$fileName} (" . $this->formatFileSize($fileSize) . ")"];

            // Split into statements
            $statements = $this->splitSql($rawSql);
            $totalStatements = count($statements);

            $log[] = ['type' => 'info', 'msg' => "Parsed: {$totalStatements} SQL statement(s) found"];

            if ($totalStatements === 0) {
                $elapsed = round((microtime(true) - $startTime) * 1000);
                return response()->json([
                    'success' => false,
                    'summary' => 'No executable SQL statements found in the file.',
                    'ok' => 0, 'errors' => 0, 'skipped' => 0, 'total' => 0,
                    'elapsed_ms' => $elapsed,
                    'log' => $log,
                ]);
            }

            // Disable FK checks
            try {
                $conn->statement('SET FOREIGN_KEY_CHECKS=0');
                $log[] = ['type' => 'info', 'msg' => 'SET FOREIGN_KEY_CHECKS=0'];
            } catch (\Exception $e) {
                $log[] = ['type' => 'warn', 'msg' => 'Could not disable FK checks: ' . $e->getMessage()];
            }

            // Execute each statement
            foreach ($statements as $idx => $stmt) {
                $stmt = trim($stmt);
                if (empty($stmt)) continue;

                // Skip pure comments that slipped through
                if (preg_match('/^--/', $stmt) || preg_match('/^\/\*.*\*\/$/s', $stmt)) {
                    $skipCount++;
                    continue;
                }

                // Build a short preview of the statement
                $preview = $this->statementPreview($stmt);
                $stmtNum = $idx + 1;

                try {
                    $conn->unprepared($stmt);
                    $okCount++;
                    $log[] = [
                        'type' => 'ok',
                        'num'  => $stmtNum,
                        'msg'  => $preview,
                    ];
                } catch (\Exception $e) {
                    $errCount++;
                    $log[] = [
                        'type'  => 'err',
                        'num'   => $stmtNum,
                        'msg'   => $preview,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Re-enable FK checks (always attempt)
            try {
                $conn->statement('SET FOREIGN_KEY_CHECKS=1');
                $log[] = ['type' => 'info', 'msg' => 'SET FOREIGN_KEY_CHECKS=1'];
            } catch (\Exception $e) {
                $log[] = ['type' => 'warn', 'msg' => 'Could not re-enable FK checks: ' . $e->getMessage()];
            }

        } catch (\Exception $e) {
            $errCount++;
            $log[] = ['type' => 'err', 'num' => 0, 'msg' => 'Fatal error', 'error' => $e->getMessage()];
        }

        $elapsed = round((microtime(true) - $startTime) * 1000);
        $allOk = $errCount === 0;

        $summary = $allOk
            ? "Import completed successfully — {$okCount} statement(s) executed"
            : "Import completed with errors — {$okCount} succeeded, {$errCount} failed";

        if ($skipCount > 0) {
            $summary .= ", {$skipCount} skipped";
        }

        $response = [
            'success'    => $allOk,
            'summary'    => $summary,
            'ok'         => $okCount,
            'errors'     => $errCount,
            'skipped'    => $skipCount,
            'total'      => $totalStatements ?? 0,
            'elapsed_ms' => $elapsed,
            'file_name'  => $fileName,
            'file_size'  => $this->formatFileSize($fileSize),
            'log'        => $log,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($response);
        }

        // Non-AJAX fallback
        return view('admin.pages.database.import', [
            'result' => $allOk ? $summary : null,
            'error'  => $allOk ? null : $summary,
        ]);
    }

    /**
     * Build a short human-readable preview of a SQL statement.
     */
    private function sqlPreview(string $sql): string
    {
        return \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $sql)), 120);
    }
}
