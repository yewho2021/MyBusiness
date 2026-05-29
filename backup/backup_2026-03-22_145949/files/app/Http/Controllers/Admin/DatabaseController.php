<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseController extends Controller
{
    /**
     * Get the isolated DB Manager connection.
     * If a saved connection is active (via session), reconfigure
     * mysql_dbmanager to use those credentials.
     * Otherwise, falls back to the default .env database.
     */
    protected function db()
    {
        $connId = session('db_connection_id');

        if ($connId) {
            $saved = DatabaseConnection::find($connId);
            if ($saved) {
                config([
                    'database.connections.mysql_dbmanager.host'     => $saved->dbhost,
                    'database.connections.mysql_dbmanager.port'     => $saved->dbport ?: '3306',
                    'database.connections.mysql_dbmanager.database' => $saved->dbname,
                    'database.connections.mysql_dbmanager.username' => $saved->dbusername,
                    'database.connections.mysql_dbmanager.password' => $saved->dbpassword,
                ]);
                DB::purge('mysql_dbmanager');
            }
        }

        return DB::connection('mysql_dbmanager');
    }

    /**
     * Get the active connection info for views.
     */
    protected function getActiveConnection(): ?DatabaseConnection
    {
        $connId = session('db_connection_id');
        return $connId ? DatabaseConnection::find($connId) : null;
    }

    /**
     * Get the active database name — from saved connection or .env.
     */
    protected function getActiveDbName(): string
    {
        $active = $this->getActiveConnection();
        return $active ? $active->dbname : config('database.connections.mysql.database');
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

    public function query(Request $request)
    {
        $sql = $request->input('sql', '');
        $results = null;
        $error = null;
        $affectedRows = null;
        $executionTime = null;
        $columns = [];

        // Internal queries use main connection (always .env database)
        $bookmarks = \App\Models\QueryBookmark::orderBy('title')->get();
        $recentHistory = \App\Models\QueryHistory::orderBy('created_at', 'desc')->limit(20)->get();

        $conn = $this->db();
        $dbName = $this->getActiveDbName();
        $activeConnection = $this->getActiveConnection();
        $savedConnections = DatabaseConnection::where('is_active', true)->orderBy('name')->get();

        $tables = $conn->select('SHOW TABLE STATUS');
        $totalSize = 0;
        $tableList = [];
        foreach ($tables as $t) {
            $size = ($t->Data_length ?? 0) + ($t->Index_length ?? 0);
            $totalSize += $size;
            $tableList[] = [
                'name' => $t->Name,
                'engine' => $t->Engine,
                'rows' => $t->Rows,
                'size' => $size,
                'collation' => $t->Collation
            ];
        }

        if ($request->isMethod('post') && !empty($sql)) {
            $sql = trim($sql);
            foreach (['DROP DATABASE', 'GRANT ', 'REVOKE ', 'CREATE USER', 'DROP USER'] as $b) {
                if (stripos($sql, $b) !== false) {
                    $error = "Blocked: '{$b}' not allowed.";
                    return view('admin.pages.database.query', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns', 'bookmarks', 'recentHistory', 'tableList', 'dbName', 'totalSize', 'activeConnection', 'savedConnections'));
                }
            }
            $start = microtime(true);
            try {
                $upper = strtoupper(ltrim($sql));
                if (str_starts_with($upper, 'SELECT') || str_starts_with($upper, 'SHOW') || str_starts_with($upper, 'DESCRIBE') || str_starts_with($upper, 'EXPLAIN')) {
                    $results = $conn->select($sql);
                    if (!empty($results))
                        $columns = array_keys((array) $results[0]);
                } else {
                    $affectedRows = $conn->affectingStatement($sql);
                }

                // Save to history on main connection (always safe)
                \App\Models\QueryHistory::create([
                    'sql_query' => $sql,
                    'created_at' => now('Asia/Kuala_Lumpur')
                ]);

            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
            $executionTime = round((microtime(true) - $start) * 1000, 2);
        }

        if ($request->ajax()) {
            return view('admin.pages.database.partials.query_result', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns'));
        }
        return view('admin.pages.database.query', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns', 'bookmarks', 'recentHistory', 'tableList', 'dbName', 'totalSize', 'activeConnection', 'savedConnections'));
    }

    public function addBookmark(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'sql_query' => 'required|string',
        ]);

        \App\Models\QueryBookmark::create([
            'title' => $request->title,
            'sql_query' => $request->sql_query,
            'created_at' => now('Asia/Kuala_Lumpur'),
        ]);

        return response()->json(['success' => true]);
    }

    public function getHistory()
    {
        $history = \App\Models\QueryHistory::orderBy('created_at', 'desc')->paginate(50);
        $bookmarks = \App\Models\QueryBookmark::orderBy('created_at', 'desc')->paginate(50);

        return view('admin.pages.database.query_history', compact('history', 'bookmarks'));
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
                            $vals = array_map(fn($v) => is_null($v) ? 'NULL' : "'" . addslashes($v) . "'", (array) $row);
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

    public function import(Request $request)
    {
        $result = null;
        $error = null;
        $conn = $this->db();
        if ($request->isMethod('post')) {
            $request->validate(['sql_file' => 'required|file|max:51200']);
            set_time_limit(600);
            try {
                $sql = file_get_contents($request->file('sql_file')->getRealPath());
                $conn->statement('SET FOREIGN_KEY_CHECKS=0');
                $stmts = $this->splitSql($sql);
                $ok = 0;
                $err = 0;
                foreach ($stmts as $s) {
                    $s = trim($s);
                    if (empty($s) || str_starts_with($s, '--') || str_starts_with($s, '/*'))
                        continue;
                    try {
                        $conn->unprepared($s);
                        $ok++;
                    } catch (\Exception $e) {
                        $err++;
                    }
                }
                $conn->statement('SET FOREIGN_KEY_CHECKS=1');
                $result = "Import completed: {$ok} statements executed" . ($err > 0 ? ", {$err} errors" : '');
            } catch (\Exception $e) {
                $error = 'Import failed: ' . $e->getMessage();
            }
        }
        if ($request->ajax()) {
            $html = '';
            if ($result)
                $html .= '<div class="success-box" style="background:#f0fdf4;border:1px solid #bbf7d0;padding:12px;border-radius:8px;color:#166534"><i class="fas fa-check-circle"></i> ' . $result . '</div>';
            if ($error)
                $html .= '<div class="error-box" style="background:#fef2f2;border:1px solid #fecaca;padding:12px;border-radius:8px;color:#991b1b"><i class="fas fa-exclamation-circle"></i> ' . $error . '</div>';
            return $html;
        }
        return view('admin.pages.database.import', compact('result', 'error'));
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
        $where = $request->input('where');
        if ($where) {
            $this->db()->delete("DELETE FROM `{$table}` WHERE {$where} LIMIT 1");
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

    protected function validateTableName(string $t): void
    {
        // Allow letters, numbers, underscores, hyphens, dots, spaces (common in external DBs)
        // Reject anything with backticks, quotes, semicolons to prevent injection
        if (!preg_match('/^[a-zA-Z0-9_ \-\.]+$/', $t))
            abort(404);
    }

    protected function splitSql(string $sql): array
    {
        $stmts = [];
        $cur = '';
        $inStr = false;
        $sc = '';
        for ($i = 0; $i < strlen($sql); $i++) {
            $c = $sql[$i];
            $p = $i > 0 ? $sql[$i - 1] : '';
            if ($inStr) {
                $cur .= $c;
                if ($c === $sc && $p !== '\\')
                    $inStr = false;
            } else {
                if ($c === '\'' || $c === '"') {
                    $inStr = true;
                    $sc = $c;
                    $cur .= $c;
                } elseif ($c === ';') {
                    $t = trim($cur);
                    if (!empty($t))
                        $stmts[] = $t;
                    $cur = '';
                } else {
                    $cur .= $c;
                }
            }
        }
        $t = trim($cur);
        if (!empty($t))
            $stmts[] = $t;
        return $stmts;
    }
}
