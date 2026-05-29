<?php

namespace App\Services;

use App\Models\Configuration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramReportBuilder
{
    private function portalName(): string
    {
        return Configuration::get('portal_name', 'Admin Portal');
    }

    // ═══════════════════════════════════════════════
    // REGISTRY — slug → method mapping
    // Built-in reports are empty by default.
    // All reports are created via the Reports page (stored in tbl_telegram_reports)
    // using either php_code or template mode.
    // To add a built-in report: (1) add method below, (2) add entry here.
    // ═══════════════════════════════════════════════
    private array $reports = [];

    // ═══════════════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════════════

    public function generate(string $slug, array $params = []): string
    {
        // Load report from DB
        $report = DB::table('tbl_telegram_reports')->where('slug', $slug)->first();

        // Priority 1: php_code in database — run directly
        if ($report && !empty($report->php_code)) {
            return $this->runFromDb($report->php_code, $params);
        }

        // Priority 2: template-based report
        if ($report && ($report->report_type ?? 'code') === 'template') {
            return $this->generateFromTemplate($report, $params);
        }

        // Priority 3: built-in method in this file
        $method = $this->reports[$slug] ?? null;
        if (!$method || !method_exists($this, $method)) {
            return "❌ Report '{$slug}' not found. No php_code in DB and no method in file.";
        }
        try {
            return $this->$method($params);
        } catch (\Throwable $e) {
            Log::error("Report generation failed", ['slug' => $slug, 'params' => $params, 'error' => $e->getMessage()]);
            return "❌ Report error: " . $e->getMessage();
        }
    }

    /**
     * Execute PHP code stored in the database.
     * Code runs in the context of this class, so $this->n(), $this->resolveDates() etc. work.
     */
    public function runFromDb(string $phpCode, array $params): string
    {
        $code = trim($phpCode);

        // Audit log: record every code execution
        Log::channel('admin')->info('Telegram report code executed', [
            'code_hash' => md5($code),
            'code_length' => strlen($code),
            'params' => array_keys($params),
        ]);

        // Strip function signature if present (keep just the body)
        if (preg_match('/function\s+\w+\s*\([^)]*\)[^{]*\{(.+)\}\s*$/s', $code, $m)) {
            $body = $m[1];
        } else {
            $body = $code;
        }

        // Security: block dangerous functions
        $blocked = ['exec', 'shell_exec', 'system', 'passthru', 'proc_open', 'popen',
                    'file_put_contents', 'unlink', 'rmdir', 'rename', 'copy', 'chmod',
                    'chown', 'symlink', 'mail', 'header', 'setcookie'];
        foreach ($blocked as $fn) {
            if (preg_match('/\b' . preg_quote($fn, '/') . '\s*\(/i', $body)) {
                Log::channel('admin')->warning('Telegram report blocked function call', [
                    'function' => $fn, 'code_hash' => md5($code),
                ]);
                return "❌ Blocked: `{$fn}()` is not allowed in report code.";
            }
        }

        try {
            $closure = eval('return function(array $params) { ' . $body . ' };');
            if (!$closure || !is_callable($closure)) {
                return '❌ Code eval failed: returned non-callable.';
            }
            return $closure->call($this, $params);
        } catch (\Throwable $e) {
            return '❌ Code error (line ' . $e->getLine() . '): ' . $e->getMessage();
        }
    }

    // ═══════════════════════════════════════════════
    // TEMPLATE ENGINE — SQL query + Markdown template
    // ═══════════════════════════════════════════════

    public function generateFromTemplate(object $report, array $params = []): string
    {
        $query = $report->query ?? '';
        $template = $report->template ?? '';
        if (!$query || !$template) return "❌ Report has no query or template defined.";

        // Safety: only SELECT allowed
        $safety = $this->validateQuery($query);
        if ($safety !== true) return "❌ SQL blocked: {$safety}";

        [$dateFrom, $dateTo] = $this->resolveDates($params);

        try {
            // Replace date placeholders in query
            $sql = str_replace(
                ['{{date_from}}', '{{date_to}}', '{{today}}'],
                ["'{$dateFrom}'", "'{$dateTo}'", "'" . Carbon::now($params['timezone'] ?? Configuration::get('default_timezone', config('app.timezone', 'UTC')))->toDateString() . "'"],
                $query
            );

            $rows = DB::select($sql);
            if (empty($rows)) {
                $vars = ['_no_data' => true, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'date_label' => $this->dateLabel($dateFrom, $dateTo)];
            } else {
                // Flatten first row into variables
                $vars = (array) $rows[0];
                $vars['date_from'] = $dateFrom;
                $vars['date_to'] = $dateTo;
                $vars['date_label'] = $this->dateLabel($dateFrom, $dateTo);
                $vars['row_count'] = count($rows);

                // Format numbers
                foreach ($vars as $k => $v) {
                    if (is_numeric($v)) {
                        $vars[$k . '_fmt'] = $this->n($v);
                        $vars[$k . '_usd'] = '$' . number_format($v, 2);
                    }
                }
            }

            // Run computed fields
            $computed = json_decode($report->computed_fields ?? '{}', true) ?: [];
            foreach ($computed as $key => $formula) {
                $vars[$key] = $this->evaluateFormula($formula, $vars);
                if (is_numeric($vars[$key])) {
                    $vars[$key . '_fmt'] = $this->n($vars[$key]);
                    $vars[$key . '_usd'] = '$' . number_format($vars[$key], 2);
                }
            }

            // Handle {{#each rows}} block for multi-row results
            $output = $template;
            if (count($rows) > 1 && str_contains($output, '{{#each}}')) {
                preg_match('/\{\{#each\}\}(.*?)\{\{\/each\}\}/s', $output, $matches);
                if ($matches) {
                    $rowTemplate = $matches[1];
                    $rowLines = '';
                    foreach ($rows as $row) {
                        $rowVars = (array) $row;
                        foreach ($rowVars as $k => $v) {
                            if (is_numeric($v)) {
                                $rowVars[$k . '_fmt'] = $this->n($v);
                                $rowVars[$k . '_usd'] = '$' . number_format($v, 2);
                            }
                        }
                        // Compute per-row
                        foreach ($computed as $key => $formula) {
                            $rowVars[$key] = $this->evaluateFormula($formula, $rowVars);
                            if (is_numeric($rowVars[$key])) {
                                $rowVars[$key . '_fmt'] = $this->n($rowVars[$key]);
                                $rowVars[$key . '_usd'] = '$' . number_format($rowVars[$key], 2);
                            }
                        }
                        $line = $rowTemplate;
                        foreach ($rowVars as $k => $v) {
                            $line = str_replace('{{' . $k . '}}', $v ?? '—', $line);
                        }
                        $rowLines .= $line;
                    }
                    $output = preg_replace('/\{\{#each\}\}.*?\{\{\/each\}\}/s', $rowLines, $output);
                }
            }

            // Replace remaining {{variable}} placeholders
            foreach ($vars as $k => $v) {
                $output = str_replace('{{' . $k . '}}', $v ?? '—', $output);
            }

            // Clean up any remaining unreplaced placeholders
            $output = preg_replace('/\{\{[a-zA-Z_][a-zA-Z0-9_]*\}\}/', '—', $output);

            return $output;

        } catch (\Throwable $e) {
            Log::error("Template report error", ['slug' => $report->slug, 'error' => $e->getMessage()]);
            return "❌ Report error: " . $e->getMessage();
        }
    }

    public function validateQuery(string $query): true|string
    {
        $upper = strtoupper(trim($query));
        // Must start with SELECT
        if (!str_starts_with($upper, 'SELECT')) {
            return "Query must start with SELECT";
        }
        // Block dangerous keywords
        $blocked = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'TRUNCATE', 'CREATE', 'GRANT', 'REVOKE', 'EXEC', 'EXECUTE', 'CALL'];
        foreach ($blocked as $word) {
            if (preg_match('/\b' . $word . '\b/i', $query)) {
                return "{$word} is not allowed";
            }
        }
        // Block multiple statements
        if (str_contains($query, ';')) {
            return "Semicolons not allowed (single statement only)";
        }
        return true;
    }

    public function testQuery(string $query, array $params = []): array
    {
        $safety = $this->validateQuery($query);
        if ($safety !== true) return ['success' => false, 'error' => "SQL blocked: {$safety}"];

        [$dateFrom, $dateTo] = $this->resolveDates($params);
        $sql = str_replace(
            ['{{date_from}}', '{{date_to}}', '{{today}}'],
            ["'{$dateFrom}'", "'{$dateTo}'", "'" . Carbon::now($params['timezone'] ?? Configuration::get('default_timezone', config('app.timezone', 'UTC')))->toDateString() . "'"],
            $query
        );

        try {
            $start = microtime(true);
            $rows = DB::select($sql);
            $ms = round((microtime(true) - $start) * 1000);
            $columns = $rows ? array_keys((array) $rows[0]) : [];
            return ['success' => true, 'rows' => $rows, 'columns' => $columns, 'count' => count($rows), 'duration_ms' => $ms];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function evaluateFormula(string $formula, array $vars): float|string
    {
        // Simple math: "spend / ftd", "regs * 100 / total"
        $expr = $formula;
        foreach ($vars as $k => $v) {
            if (is_numeric($v)) {
                $expr = preg_replace('/\b' . preg_quote($k, '/') . '\b/', (string)$v, $expr);
            }
        }
        // Safety: only allow numbers, operators, parentheses, spaces
        if (!preg_match('/^[\d\s\.\+\-\*\/\(\)]+$/', $expr)) return '—';
        // Prevent division by zero
        if (preg_match('/\/\s*0(\s|$|\))/', $expr)) return 0;
        try {
            $result = @eval("return ({$expr});");
            return is_numeric($result) ? round($result, 2) : '—';
        } catch (\Throwable $e) {
            return '—';
        }
    }

    public function availableSlugs(): array
    {
        return array_keys($this->reports);
    }

    public function getUnregistered(): array
    {
        $registered = DB::table('tbl_telegram_reports')->pluck('slug')->toArray();
        $unregistered = [];
        foreach ($this->reports as $slug => $method) {
            if (!in_array($slug, $registered)) {
                $unregistered[] = ['slug' => $slug, 'method' => $method];
            }
        }
        return $unregistered;
    }

    public function getOrphaned(): array
    {
        $dbSlugs = DB::table('tbl_telegram_reports')->pluck('slug', 'id')->toArray();
        $orphaned = [];
        foreach ($dbSlugs as $id => $slug) {
            if (!isset($this->reports[$slug])) {
                $orphaned[] = ['id' => $id, 'slug' => $slug];
            }
        }
        return $orphaned;
    }

    // ═══════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════

    private function resolveDates(array $params): array
    {
        $range = $params['date_range'] ?? 'yesterday';
        $tz = $params['timezone'] ?? Configuration::get('default_timezone', config('app.timezone', 'UTC'));
        $now = Carbon::now($tz);

        return match ($range) {
            'today'      => [$now->toDateString(), $now->toDateString()],
            'yesterday'  => [$now->copy()->subDay()->toDateString(), $now->copy()->subDay()->toDateString()],
            'last_7d'    => [$now->copy()->subDays(7)->toDateString(), $now->copy()->subDay()->toDateString()],
            'last_30d'   => [$now->copy()->subDays(30)->toDateString(), $now->copy()->subDay()->toDateString()],
            'this_week'  => [$now->copy()->startOfWeek()->toDateString(), $now->toDateString()],
            'last_week'  => [$now->copy()->subWeek()->startOfWeek()->toDateString(), $now->copy()->subWeek()->endOfWeek()->toDateString()],
            'this_month' => [$now->copy()->startOfMonth()->toDateString(), $now->toDateString()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()],
            default      => [$params['date_from'] ?? $now->toDateString(), $params['date_to'] ?? $now->toDateString()],
        };
    }

    private function n(int|float $n): string { return number_format($n); }
    private function usd(float $n): string { return '$' . number_format($n, 2); }
    private function pct(float $n): string { return round($n, 1) . '%'; }

    private function dateLabel(string $from, string $to): string
    {
        return $from === $to ? $from : "{$from} → {$to}";
    }

    // ═══════════════════════════════════════════════
    // BUILT-IN REPORT METHODS
    // ═══════════════════════════════════════════════
    // No built-in reports — this portal is tenant-agnostic.
    // All reports are created via the Reports page and stored
    // in tbl_telegram_reports using php_code or template mode.
    // The generate() method dispatches to DB code first, then
    // falls back to the $reports registry above (currently empty).
    //
    // To add a built-in report:
    //   1. Add a private method: private function buildMyReport(array $params): string { ... }
    //   2. Register it: 'my_report' => 'buildMyReport' in $reports array above
    // ═══════════════════════════════════════════════

}
