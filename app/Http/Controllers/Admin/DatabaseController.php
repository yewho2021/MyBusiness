<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseConnection;
use App\Traits\DatabaseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;

class DatabaseController extends Controller implements HasMiddleware
{
    use DatabaseHelpers;

    public static function middleware(): array
    {
        return static::adminOnlyMiddleware();
    }

    public function index()
    {
        return redirect()->route('admin.database.query');
    }

    public function viewTable($table)
    {
        $this->validateTableName($table);
        $conn = $this->db();
        $columns = $conn->select("SHOW FULL COLUMNS FROM `{$table}`");
        $indexes = $conn->select("SHOW INDEX FROM `{$table}`");
        $cnt = $conn->select("SELECT COUNT(*) as cnt FROM `{$table}`");
        $totalRows = $cnt[0]->cnt ?? 0;
        $perPage = min(1000, max(50, (int) request('perPage', 50)));
        $page = max(1, (int) request('page', 1));
        $offset = ($page - 1) * $perPage;

        // Sorting
        $sortCol = request('sort', '');
        $sortDir = strtolower(request('dir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $orderBy = '';
        if ($sortCol && preg_match('/^[a-zA-Z0-9_ \-\.]+$/', $sortCol)) {
            $orderBy = " ORDER BY `{$sortCol}` {$sortDir}";
        }

        $rows = $conn->select("SELECT * FROM `{$table}`{$orderBy} LIMIT {$perPage} OFFSET {$offset}");
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $status = $conn->select("SHOW TABLE STATUS LIKE '{$table}'");
        $tableInfo = $status[0] ?? null;
        $createResult = $conn->select("SHOW CREATE TABLE `{$table}`");
        $createSql = $createResult[0]->{'Create Table'} ?? '';
        // Detect primary key column
        $pkColumn = null;
        foreach ($columns as $col) {
            if ($col->Key === 'PRI') {
                $pkColumn = $col->Field;
                break;
            }
        }
        if (request()->ajax()) {
            return view('admin.pages.database.partials.table_content', compact('table', 'columns', 'indexes', 'rows', 'totalRows', 'page', 'perPage', 'totalPages', 'tableInfo', 'createSql', 'offset', 'pkColumn', 'sortCol', 'sortDir'));
        }
        return view('admin.pages.database.table', compact('table', 'columns', 'indexes', 'rows', 'totalRows', 'page', 'perPage', 'totalPages', 'tableInfo', 'createSql', 'offset', 'pkColumn', 'sortCol', 'sortDir'));
    }

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

    public function dropTable($table)
    {
        $this->validateTableName($table);
        $this->db()->statement("DROP TABLE IF EXISTS `{$table}`");
        return redirect()->route('admin.database.index')->with('success', "Table `{$table}` dropped.");
    }

    public function truncateTable($table)
    {
        $this->validateTableName($table);
        $this->db()->statement("TRUNCATE TABLE `{$table}`");
        return redirect()->route('admin.database.table', $table)->with('success', "Table `{$table}` truncated.");
    }

    public function deleteRow(Request $request, $table)
    {
        $this->validateTableName($table);

        $pkColumn = $request->input('pk_column');
        $pkValue = $request->input('pk_value');

        // Validate PK column name (alphanumeric + underscore only)
        if (!$pkColumn || !preg_match('/^[a-zA-Z0-9_]+$/', $pkColumn)) {
            return response()->json(['success' => false, 'message' => 'Invalid primary key column.'], 400);
        }

        if ($pkValue === null || $pkValue === '') {
            return response()->json(['success' => false, 'message' => 'Missing primary key value.'], 400);
        }

        try {
            $this->db()->delete("DELETE FROM `{$table}` WHERE `{$pkColumn}` = ? LIMIT 1", [$pkValue]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()], 500);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.database.table', ['table' => $table, 'page' => $request->input('page', 1)])->with('success', 'Row deleted.');
    }

    public function updateCell(Request $request, $table)
    {
        $this->validateTableName($table);
        $column = $request->input('column');
        $value = $request->input('value');
        $pkColumn = $request->input('pk_column');
        $pkValue = $request->input('pk_value');

        if (!$column || !$pkColumn) {
            return response()->json(['success' => false, 'message' => 'Missing parameters'], 400);
        }

        // Validate column name
        if (!preg_match('/^[a-zA-Z0-9_ \-\.]+$/', $column) || !preg_match('/^[a-zA-Z0-9_ \-\.]+$/', $pkColumn)) {
            return response()->json(['success' => false, 'message' => 'Invalid column name'], 400);
        }

        try {
            $newValue = ($value === '' || $value === null) ? null : $value;
            $this->db()->table($table)->where($pkColumn, $pkValue)->update([$column => $newValue]);
            return response()->json(['success' => true, 'message' => 'Updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
