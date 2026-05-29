<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
