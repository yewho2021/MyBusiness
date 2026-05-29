<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseConnection;
use App\Traits\DatabaseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;

class QueryController extends Controller implements HasMiddleware
{
    use DatabaseHelpers;

    public static function middleware(): array
    {
        return static::adminOnlyMiddleware();
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

        if ($request->isMethod('post') && ($request->has('sql') || $request->has('sql_b64'))) {
            // Accept base64-encoded SQL (bypasses cPanel mod_security blocking raw SQL in POST body)
            if ($request->has('sql_b64')) {
                $sql = base64_decode($request->input('sql_b64'));
                if ($sql === false) {
                    $error = 'Invalid base64-encoded SQL.';
                    if ($request->ajax()) {
                        return view('admin.pages.database.partials.query_result', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns'));
                    }
                    return view('admin.pages.database.query', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns', 'bookmarks', 'recentHistory', 'tableList', 'dbName', 'totalSize', 'activeConnection', 'savedConnections'));
                }
            } else {
                $sql = $request->input('sql', '');
            }
            $sql = trim($sql);
            if (empty($sql)) {
                if ($request->ajax()) {
                    return view('admin.pages.database.partials.query_result', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns'));
                }
                return view('admin.pages.database.query', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns', 'bookmarks', 'recentHistory', 'tableList', 'dbName', 'totalSize', 'activeConnection', 'savedConnections'));
            }

            // Blocked commands check (applies to entire input)
            foreach (['DROP DATABASE', 'GRANT ', 'REVOKE ', 'CREATE USER', 'DROP USER'] as $b) {
                if (stripos($sql, $b) !== false) {
                    $error = "Blocked: '{$b}' not allowed.";
                    if ($request->ajax()) {
                        return view('admin.pages.database.partials.query_result', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns'));
                    }
                    return view('admin.pages.database.query', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns', 'bookmarks', 'recentHistory', 'tableList', 'dbName', 'totalSize', 'activeConnection', 'savedConnections'));
                }
            }

            // Split into individual statements
            $statements = $this->splitSql($sql);

            // Single statement → original behavior (backward compatible)
            if (count($statements) === 1) {
                $singleSql = $statements[0];
                $start = microtime(true);
                try {
                    $upper = strtoupper(ltrim($singleSql));
                    if (str_starts_with($upper, 'SELECT') || str_starts_with($upper, 'SHOW') || str_starts_with($upper, 'DESCRIBE') || str_starts_with($upper, 'EXPLAIN')) {
                        $results = $conn->select($singleSql);
                        if (!empty($results))
                            $columns = array_keys((array) $results[0]);
                    } else {
                        $affectedRows = $conn->affectingStatement($singleSql);
                    }
                    \App\Models\QueryHistory::create([
                        'admin_id' => $request->attributes->get('admin')?->id,
                        'sql_query' => $singleSql,
                        'created_at' => now()
                    ]);
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
                $executionTime = round((microtime(true) - $start) * 1000, 2);

                if ($request->ajax()) {
                    return view('admin.pages.database.partials.query_result', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns'));
                }
                return view('admin.pages.database.query', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns', 'bookmarks', 'recentHistory', 'tableList', 'dbName', 'totalSize', 'activeConnection', 'savedConnections'));
            }

            // Multi-statement → execute each, collect results
            $multiResults = [];
            $totalStart = microtime(true);

            foreach ($statements as $idx => $stmt) {
                $stmtResult = [
                    'index' => $idx + 1,
                    'sql' => $stmt,
                    'sql_preview' => \Illuminate\Support\Str::limit($stmt, 120),
                    'status' => 'success',
                    'results' => null,
                    'columns' => [],
                    'affected_rows' => null,
                    'error' => null,
                    'time_ms' => 0,
                ];

                $stmtStart = microtime(true);
                try {
                    $upper = strtoupper(ltrim($stmt));
                    if (str_starts_with($upper, 'SELECT') || str_starts_with($upper, 'SHOW') || str_starts_with($upper, 'DESCRIBE') || str_starts_with($upper, 'EXPLAIN')) {
                        $stmtResult['results'] = $conn->select($stmt);
                        if (!empty($stmtResult['results'])) {
                            $stmtResult['columns'] = array_keys((array) $stmtResult['results'][0]);
                        }
                        $stmtResult['row_count'] = count($stmtResult['results']);
                    } else {
                        $stmtResult['affected_rows'] = $conn->affectingStatement($stmt);
                    }
                } catch (\Exception $e) {
                    $stmtResult['status'] = 'error';
                    $stmtResult['error'] = $e->getMessage();
                }
                $stmtResult['time_ms'] = round((microtime(true) - $stmtStart) * 1000, 2);
                $multiResults[] = $stmtResult;
            }

            $totalTime = round((microtime(true) - $totalStart) * 1000, 2);

            // Save entire batch to history
            \App\Models\QueryHistory::create([
                'admin_id' => $request->attributes->get('admin')?->id,
                'sql_query' => \Illuminate\Support\Str::limit($sql, 2000),
                'created_at' => now()
            ]);

            if ($request->ajax()) {
                return view('admin.pages.database.partials.query_result_multi', compact('multiResults', 'totalTime'));
            }
            return view('admin.pages.database.query', compact('sql', 'results', 'error', 'affectedRows', 'executionTime', 'columns', 'bookmarks', 'recentHistory', 'tableList', 'dbName', 'totalSize', 'activeConnection', 'savedConnections'));
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
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function getHistory()
    {
        $history = \App\Models\QueryHistory::orderBy('created_at', 'desc')->paginate(50);
        $bookmarks = \App\Models\QueryBookmark::orderBy('created_at', 'desc')->paginate(50);

        return view('admin.pages.database.query_history', compact('history', 'bookmarks'));
    }
    protected function statementPreview(string $stmt): string
    {
        // Normalize whitespace
        $clean = preg_replace('/\s+/', ' ', trim($stmt));

        // Extract operation and target
        if (preg_match('/^(CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?)\s+`?(\w+)`?/i', $clean, $m)) {
            return strtoupper($m[1]) . " `{$m[2]}`";
        }
        if (preg_match('/^(DROP\s+TABLE(?:\s+IF\s+EXISTS)?)\s+`?(\w+)`?/i', $clean, $m)) {
            return strtoupper($m[1]) . " `{$m[2]}`";
        }
        if (preg_match('/^(ALTER\s+TABLE)\s+`?(\w+)`?/i', $clean, $m)) {
            return "ALTER TABLE `{$m[2]}`";
        }
        if (preg_match('/^(INSERT\s+INTO)\s+`?(\w+)`?/i', $clean, $m)) {
            // Count approximate rows
            $rows = substr_count($clean, '),(') + 1;
            return "INSERT INTO `{$m[2]}` ({$rows} row" . ($rows > 1 ? 's' : '') . ")";
        }
        if (preg_match('/^(UPDATE)\s+`?(\w+)`?/i', $clean, $m)) {
            return "UPDATE `{$m[2]}`";
        }
        if (preg_match('/^(DELETE\s+FROM)\s+`?(\w+)`?/i', $clean, $m)) {
            return "DELETE FROM `{$m[2]}`";
        }
        if (preg_match('/^(TRUNCATE(?:\s+TABLE)?)\s+`?(\w+)`?/i', $clean, $m)) {
            return "TRUNCATE `{$m[2]}`";
        }
        if (preg_match('/^(CREATE\s+(?:UNIQUE\s+)?INDEX)\s+`?(\w+)`?\s+ON\s+`?(\w+)`?/i', $clean, $m)) {
            return "CREATE INDEX `{$m[2]}` ON `{$m[3]}`";
        }
        if (preg_match('/^SET\s+/i', $clean)) {
            return mb_substr($clean, 0, 60) . (mb_strlen($clean) > 60 ? '...' : '');
        }

        // Fallback: first 80 chars
        return mb_substr($clean, 0, 80) . (mb_strlen($clean) > 80 ? '...' : '');
    }
    protected function splitSql(string $sql): array
    {
        $stmts = [];
        $len = strlen($sql);
        $cur = '';
        $delimiter = ';';
        $i = 0;

        while ($i < $len) {
            $c = $sql[$i];

            // ── DELIMITER command (e.g. "DELIMITER $$" or "DELIMITER ;") ──
            if ($cur === '' || trim($cur) === '') {
                $remaining = substr($sql, $i, 20);
                if (preg_match('/^DELIMITER\s+(\S+)/i', $remaining, $dm)) {
                    $delimiter = $dm[1];
                    $i += strlen($dm[0]);
                    $cur = '';
                    // Skip to end of line
                    while ($i < $len && $sql[$i] !== "\n") $i++;
                    if ($i < $len) $i++; // skip \n
                    continue;
                }
            }

            // ── Single-line comment: -- ──
            if ($c === '-' && $i + 1 < $len && $sql[$i + 1] === '-') {
                // Skip to end of line
                while ($i < $len && $sql[$i] !== "\n") $i++;
                if ($i < $len) $i++; // skip \n
                // Add a space to prevent token merging
                if (trim($cur) !== '') $cur .= ' ';
                continue;
            }

            // ── Single-line comment: # ──
            if ($c === '#') {
                while ($i < $len && $sql[$i] !== "\n") $i++;
                if ($i < $len) $i++;
                if (trim($cur) !== '') $cur .= ' ';
                continue;
            }

            // ── Block comment: /* ... */ ──
            if ($c === '/' && $i + 1 < $len && $sql[$i + 1] === '*') {
                // Check for MySQL conditional: /*!40101 ... */
                $isConditional = ($i + 2 < $len && $sql[$i + 2] === '!');
                $i += 2; // skip /*
                $commentBody = '';
                while ($i < $len) {
                    if ($sql[$i] === '*' && $i + 1 < $len && $sql[$i + 1] === '/') {
                        $i += 2; // skip */
                        break;
                    }
                    $commentBody .= $sql[$i];
                    $i++;
                }
                // For conditional comments like /*!40101 SET ... */, extract the SQL
                if ($isConditional && preg_match('/^!\d+\s+(.+)$/s', $commentBody, $cm)) {
                    $cur .= $cm[1];
                }
                continue;
            }

            // ── Quoted string: ' or " ──
            if ($c === '\'' || $c === '"') {
                $quote = $c;
                $cur .= $c;
                $i++;
                while ($i < $len) {
                    $sc = $sql[$i];
                    $cur .= $sc;
                    if ($sc === '\\') {
                        // Escaped char — consume next char unconditionally
                        $i++;
                        if ($i < $len) {
                            $cur .= $sql[$i];
                        }
                    } elseif ($sc === $quote) {
                        // Check for doubled quote escape: '' or ""
                        if ($i + 1 < $len && $sql[$i + 1] === $quote) {
                            $cur .= $sql[$i + 1];
                            $i++; // skip the doubled quote
                        } else {
                            break; // end of string
                        }
                    }
                    $i++;
                }
                $i++;
                continue;
            }

            // ── Backtick identifier ──
            if ($c === '`') {
                $cur .= $c;
                $i++;
                while ($i < $len) {
                    $cur .= $sql[$i];
                    if ($sql[$i] === '`') {
                        // Check for doubled backtick: ``
                        if ($i + 1 < $len && $sql[$i + 1] === '`') {
                            $cur .= $sql[$i + 1];
                            $i++;
                        } else {
                            break;
                        }
                    }
                    $i++;
                }
                $i++;
                continue;
            }

            // ── Delimiter check ──
            $delimLen = strlen($delimiter);
            if (substr($sql, $i, $delimLen) === $delimiter) {
                $stmt = trim($cur);
                if ($stmt !== '') {
                    $stmts[] = $stmt;
                }
                $cur = '';
                $i += $delimLen;
                continue;
            }

            // ── Regular character ──
            $cur .= $c;
            $i++;
        }

        // Remaining buffer
        $stmt = trim($cur);
        if ($stmt !== '') {
            $stmts[] = $stmt;
        }

        return $stmts;
    }
}
